<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\MincienciasProduct;
use App\Models\ResearchLine;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-029
 * El co_investigador no tiene training_center_id (no es un rol "bound" a
 * sede). Ahora elige el centro al crear el producto Minciencias, y solo el
 * administrador_sistema de ESE centro puede verlo/aprobarlo/rechazarlo —
 * nunca el de otro centro.
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

    private function crearCoInvestigador(): User
    {
        $user = User::factory()->create(['training_center_id' => null]);
        $user->assignRole('co_investigador');

        return $user;
    }

    public function test_co_investigador_elige_centro_al_crear_el_producto(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('Centro A');
        $coInv = $this->crearCoInvestigador();
        $linea = ResearchLine::create(['nombre' => 'Linea test']);

        $response = $this->actingAs($coInv)->post(route('co-investigador.productos.store'), [
            'nombre' => 'Producto vinculado a centro',
            'research_line_id' => $linea->id,
            'training_center_id' => $centro->id,
        ]);

        $producto = MincienciasProduct::first();
        $response->assertRedirect(route('co-investigador.productos.show', $producto));
        $this->assertSame($centro->id, $producto->training_center_id);
        $this->assertSame(EstadoRevisionEnum::Pendiente, $producto->estado_revision);
    }

    public function test_admin_del_mismo_centro_puede_ver_y_aprobar(): void
    {
        $centro = $this->crearCentro('Centro B');
        $admin = $this->crearAdmin($centro);
        $coInv = $this->crearCoInvestigador();
        $linea = ResearchLine::create(['nombre' => 'Linea test']);

        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id,
            'training_center_id' => $centro->id,
            'research_line_id' => $linea->id,
            'nombre' => 'Producto centro B',
            'estado' => EstadoEnum::Activo,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $this->actingAs($admin)->get(route('admin.minciencias.show', $producto))->assertOk();

        $this->actingAs($admin)->post(route('admin.minciencias.aprobar', $producto))
            ->assertRedirect(route('admin.minciencias.index'));

        $producto->refresh();
        $this->assertSame(EstadoRevisionEnum::Aprobado, $producto->estado_revision);
        $this->assertSame($admin->id, $producto->revisado_por);
        $this->assertNotNull($producto->revisado_at);
    }

    public function test_admin_de_otro_centro_no_puede_ver_ni_aprobar(): void
    {
        $centroDelProducto = $this->crearCentro('Centro C');
        $otroCentro = $this->crearCentro('Centro D');
        $adminAjeno = $this->crearAdmin($otroCentro);
        $coInv = $this->crearCoInvestigador();
        $linea = ResearchLine::create(['nombre' => 'Linea test']);

        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id,
            'training_center_id' => $centroDelProducto->id,
            'research_line_id' => $linea->id,
            'nombre' => 'Producto centro C',
            'estado' => EstadoEnum::Activo,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $this->actingAs($adminAjeno)->get(route('admin.minciencias.show', $producto))->assertForbidden();
        $this->actingAs($adminAjeno)->post(route('admin.minciencias.aprobar', $producto))->assertForbidden();

        $producto->refresh();
        $this->assertSame(EstadoRevisionEnum::Pendiente, $producto->estado_revision);
    }

    public function test_indice_del_admin_solo_muestra_productos_de_su_propio_centro(): void
    {
        $centroPropio = $this->crearCentro('Centro E');
        $centroAjeno = $this->crearCentro('Centro F');
        $admin = $this->crearAdmin($centroPropio);
        $coInv = $this->crearCoInvestigador();
        $linea = ResearchLine::create(['nombre' => 'Linea test']);

        MincienciasProduct::create([
            'user_id' => $coInv->id, 'training_center_id' => $centroPropio->id,
            'research_line_id' => $linea->id, 'nombre' => 'Visible para mi centro',
            'estado' => EstadoEnum::Activo, 'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);
        MincienciasProduct::create([
            'user_id' => $coInv->id, 'training_center_id' => $centroAjeno->id,
            'research_line_id' => $linea->id, 'nombre' => 'No debe verse',
            'estado' => EstadoEnum::Activo, 'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.minciencias.index'));

        $response->assertOk();
        $response->assertSee('Visible para mi centro');
        $response->assertDontSee('No debe verse');
    }

    public function test_rechazar_requiere_observaciones_y_actualiza_estado(): void
    {
        $centro = $this->crearCentro('Centro G');
        $admin = $this->crearAdmin($centro);
        $coInv = $this->crearCoInvestigador();
        $linea = ResearchLine::create(['nombre' => 'Linea test']);

        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id, 'training_center_id' => $centro->id,
            'research_line_id' => $linea->id, 'nombre' => 'Producto a rechazar',
            'estado' => EstadoEnum::Activo, 'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $this->actingAs($admin)->post(route('admin.minciencias.rechazar', $producto), [])
            ->assertSessionHasErrors('observaciones');

        $this->actingAs($admin)->post(route('admin.minciencias.rechazar', $producto), [
            'observaciones' => 'Falta el soporte documental.',
        ])->assertRedirect(route('admin.minciencias.index'));

        $producto->refresh();
        $this->assertSame(EstadoRevisionEnum::Rechazado, $producto->estado_revision);
        $this->assertSame('Falta el soporte documental.', $producto->observacion_admin);
    }
}
