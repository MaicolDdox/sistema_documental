<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\GrupoInvestigacion;
use App\Models\MincienciasProduct;
use App\Models\ResearchLine;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-029 (reescrito para la reforma de roles GDI/SDI).
 *
 * Original: el co_investigador (sin training_center_id) elegía el centro al
 * crear el producto Minciencias, y solo el administrador_sistema de ESE
 * centro podía verlo/aprobarlo/rechazarlo.
 *
 * Tras la reforma: co_investigador_gdi SÍ tiene training_center_id (se
 * hereda de su usuario, ya no se elige en el formulario) y queda vinculado a
 * un grupo_investigacion (heredado de quien lo creó, director_grupo_investigacion).
 * Ahora es director_grupo_investigacion quien aprueba/rechaza — únicamente
 * los productos de SU propio grupo, no los de otro grupo aunque compartan
 * centro. administrador_sistema conserva index/show de solo lectura por
 * centro, pero ya no aprueba ni rechaza.
 */
class BUG20260813029Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(string $nombre): TrainingCenter
    {
        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-029']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-029', 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => $nombre, 'codigo' => 'BUG029-'.$nombre, 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    private function crearAdmin(TrainingCenter $centro): User
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');

        return $admin;
    }

    private function crearGrupoConDirector(TrainingCenter $centro, string $codigo): array
    {
        $director = User::factory()->create(['training_center_id' => $centro->id]);
        $director->assignRole('director_grupo_investigacion');

        $grupo = GrupoInvestigacion::create([
            'training_center_id' => $centro->id,
            'creator_id' => $director->id,
            'director_id' => $director->id,
            'nombre' => "Grupo {$codigo}",
            'codigo' => $codigo,
            'estado' => EstadoEnum::Activo,
        ]);

        return [$director, $grupo];
    }

    private function crearCoInvestigadorGdi(TrainingCenter $centro, GrupoInvestigacion $grupo): User
    {
        $user = User::factory()->create([
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => $grupo->id,
        ]);
        $user->assignRole('co_investigador_gdi');

        return $user;
    }

    public function test_co_investigador_gdi_hereda_centro_y_grupo_al_crear_el_producto(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('Centro A');
        [, $grupo] = $this->crearGrupoConDirector($centro, 'A1');
        $coInv = $this->crearCoInvestigadorGdi($centro, $grupo);
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea test']);

        $response = $this->actingAs($coInv)->post(route('co-investigador-gdi.productos.store'), [
            'nombre' => 'Producto vinculado a grupo',
            'research_line_id' => $linea->id,
            // Intento de override: el formulario ya no ofrece este campo, y
            // aunque llegara en el request, debe ignorarse.
            'training_center_id' => 99999,
        ]);

        $producto = MincienciasProduct::first();
        $response->assertRedirect(route('co-investigador-gdi.productos.show', $producto));
        $this->assertSame($centro->id, $producto->training_center_id);
        $this->assertSame($grupo->id, $producto->grupo_investigacion_id);
        $this->assertSame(EstadoRevisionEnum::Pendiente, $producto->estado_revision);
    }

    public function test_director_de_su_propio_grupo_puede_ver_y_aprobar(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('Centro B');
        [$director, $grupo] = $this->crearGrupoConDirector($centro, 'B1');
        $coInv = $this->crearCoInvestigadorGdi($centro, $grupo);
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea test']);

        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id,
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => $grupo->id,
            'research_line_id' => $linea->id,
            'nombre' => 'Producto grupo B1',
            'estado' => EstadoEnum::Activo,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $this->actingAs($director)->get(route('director-grupo-investigacion.minciencias.show', $producto))->assertOk();

        $this->actingAs($director)->post(route('director-grupo-investigacion.minciencias.aprobar', $producto))
            ->assertRedirect(route('director-grupo-investigacion.minciencias.index'));

        $producto->refresh();
        $this->assertSame(EstadoRevisionEnum::Aprobado, $producto->estado_revision);
        $this->assertSame($director->id, $producto->revisado_por);
        $this->assertNotNull($producto->revisado_at);
    }

    public function test_director_de_otro_grupo_no_puede_ver_ni_aprobar_aunque_comparta_centro(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('Centro C');
        [, $grupoDelProducto] = $this->crearGrupoConDirector($centro, 'C1');
        [$directorAjeno] = $this->crearGrupoConDirector($centro, 'C2');
        $coInv = $this->crearCoInvestigadorGdi($centro, $grupoDelProducto);
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea test']);

        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id,
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => $grupoDelProducto->id,
            'research_line_id' => $linea->id,
            'nombre' => 'Producto grupo C1',
            'estado' => EstadoEnum::Activo,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $this->actingAs($directorAjeno)->get(route('director-grupo-investigacion.minciencias.show', $producto))->assertForbidden();
        $this->actingAs($directorAjeno)->post(route('director-grupo-investigacion.minciencias.aprobar', $producto))->assertForbidden();

        $producto->refresh();
        $this->assertSame(EstadoRevisionEnum::Pendiente, $producto->estado_revision);
    }

    public function test_indice_del_director_solo_muestra_productos_de_su_propio_grupo(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('Centro E');
        [$director, $grupoPropio] = $this->crearGrupoConDirector($centro, 'E1');
        [, $grupoAjeno] = $this->crearGrupoConDirector($centro, 'E2');
        $coInvPropio = $this->crearCoInvestigadorGdi($centro, $grupoPropio);
        $coInvAjeno = $this->crearCoInvestigadorGdi($centro, $grupoAjeno);
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea test']);

        MincienciasProduct::create([
            'user_id' => $coInvPropio->id, 'training_center_id' => $centro->id, 'grupo_investigacion_id' => $grupoPropio->id,
            'research_line_id' => $linea->id, 'nombre' => 'Visible para mi grupo',
            'estado' => EstadoEnum::Activo, 'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);
        MincienciasProduct::create([
            'user_id' => $coInvAjeno->id, 'training_center_id' => $centro->id, 'grupo_investigacion_id' => $grupoAjeno->id,
            'research_line_id' => $linea->id, 'nombre' => 'No debe verse',
            'estado' => EstadoEnum::Activo, 'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $response = $this->actingAs($director)->get(route('director-grupo-investigacion.minciencias.index'));

        $response->assertOk();
        $response->assertSee('Visible para mi grupo');
        $response->assertDontSee('No debe verse');
    }

    public function test_rechazar_requiere_observaciones_y_actualiza_estado(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('Centro G');
        [$director, $grupo] = $this->crearGrupoConDirector($centro, 'G1');
        $coInv = $this->crearCoInvestigadorGdi($centro, $grupo);
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea test']);

        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id, 'training_center_id' => $centro->id, 'grupo_investigacion_id' => $grupo->id,
            'research_line_id' => $linea->id, 'nombre' => 'Producto a rechazar',
            'estado' => EstadoEnum::Activo, 'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $this->actingAs($director)->post(route('director-grupo-investigacion.minciencias.rechazar', $producto), [])
            ->assertSessionHasErrors('observaciones');

        $this->actingAs($director)->post(route('director-grupo-investigacion.minciencias.rechazar', $producto), [
            'observaciones' => 'Falta el soporte documental.',
        ])->assertRedirect(route('director-grupo-investigacion.minciencias.index'));

        $producto->refresh();
        $this->assertSame(EstadoRevisionEnum::Rechazado, $producto->estado_revision);
        $this->assertSame('Falta el soporte documental.', $producto->observacion_admin);
    }

    public function test_administrador_sistema_conserva_solo_lectura_pero_ya_no_puede_aprobar_ni_rechazar(): void
    {
        $centro = $this->crearCentro('Centro H');
        $admin = $this->crearAdmin($centro);
        [, $grupo] = $this->crearGrupoConDirector($centro, 'H1');
        $coInv = $this->crearCoInvestigadorGdi($centro, $grupo);
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea test']);

        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id, 'training_center_id' => $centro->id, 'grupo_investigacion_id' => $grupo->id,
            'research_line_id' => $linea->id, 'nombre' => 'Producto centro H',
            'estado' => EstadoEnum::Activo, 'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $this->actingAs($admin)->get(route('admin.minciencias.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.minciencias.show', $producto))->assertOk();

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.minciencias.aprobar'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.minciencias.rechazar'));

        $producto->refresh();
        $this->assertSame(EstadoRevisionEnum::Pendiente, $producto->estado_revision);
    }
}
