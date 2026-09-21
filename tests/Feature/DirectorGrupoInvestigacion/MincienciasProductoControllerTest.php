<?php

namespace Tests\Feature\DirectorGrupoInvestigacion;

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
 * Reforma GDI/SDI: director_grupo_investigacion aprueba/rechaza SOLO los
 * productos Minciencias de los co_investigador_gdi de SU PROPIO grupo — el
 * aislamiento es por grupo, no por centro (dos grupos pueden compartir
 * centro sin que sus directores se vean entre sí).
 */
class MincienciasProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearGrupoConDirectorYProducto(TrainingCenter $centro, string $codigo): array
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

        $coInv = User::factory()->create(['training_center_id' => $centro->id, 'grupo_investigacion_id' => $grupo->id]);
        $coInv->assignRole('co_investigador_gdi');

        $linea = ResearchLine::firstOrCreate(['training_center_id' => $centro->id, 'nombre' => 'Linea DGI']);
        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id,
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => $grupo->id,
            'research_line_id' => $linea->id,
            'nombre' => "Producto {$codigo}",
            'estado' => EstadoEnum::Activo,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        return [$director, $grupo, $producto];
    }

    public function test_dos_grupos_del_mismo_centro_quedan_aislados_entre_si(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto DGI']);
        $ciudad = City::create(['nombre' => 'Ciudad DGI', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro DGI', 'codigo' => 'DGI-001', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        [$directorUno, , $productoUno] = $this->crearGrupoConDirectorYProducto($centro, 'DGI-G1');
        [$directorDos, , $productoDos] = $this->crearGrupoConDirectorYProducto($centro, 'DGI-G2');

        // Cada director ve solo el producto de su propio grupo.
        $this->actingAs($directorUno)->get(route('director-grupo-investigacion.minciencias.index'))
            ->assertOk()->assertSee($productoUno->nombre)->assertDontSee($productoDos->nombre);

        $this->actingAs($directorDos)->get(route('director-grupo-investigacion.minciencias.index'))
            ->assertOk()->assertSee($productoDos->nombre)->assertDontSee($productoUno->nombre);

        // Ninguno puede ver ni aprobar el producto del otro grupo.
        $this->actingAs($directorUno)->get(route('director-grupo-investigacion.minciencias.show', $productoDos))
            ->assertForbidden();
        $this->actingAs($directorUno)->post(route('director-grupo-investigacion.minciencias.aprobar', $productoDos))
            ->assertForbidden();

        $productoDos->refresh();
        $this->assertSame(EstadoRevisionEnum::Pendiente, $productoDos->estado_revision);
    }

    public function test_director_aprueba_producto_de_su_propio_grupo(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto DGI2']);
        $ciudad = City::create(['nombre' => 'Ciudad DGI2', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro DGI2', 'codigo' => 'DGI-002', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        [$director, , $producto] = $this->crearGrupoConDirectorYProducto($centro, 'DGI-G3');

        $this->actingAs($director)->post(route('director-grupo-investigacion.minciencias.aprobar', $producto))
            ->assertRedirect(route('director-grupo-investigacion.minciencias.index'));

        $producto->refresh();
        $this->assertSame(EstadoRevisionEnum::Aprobado, $producto->estado_revision);
        $this->assertSame($director->id, $producto->revisado_por);
    }
}
