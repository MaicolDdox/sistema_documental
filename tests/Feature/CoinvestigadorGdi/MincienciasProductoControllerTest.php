<?php

namespace Tests\Feature\CoinvestigadorGdi;

use App\Enums\EstadoEnum;
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
 * Reforma GDI/SDI: co_investigador_gdi ya no elige training_center_id al
 * crear un producto Minciencias (a diferencia del co_investigador original,
 * BUG-20260813-029) — se hereda de su propio usuario, igual que
 * grupo_investigacion_id (heredado del director que creó la cuenta).
 */
class MincienciasProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearCoInvestigadorGdiConGrupo(): array
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto GDI']);
        $ciudad = City::create(['nombre' => 'Ciudad GDI', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro GDI', 'codigo' => 'GDI-001', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $director = User::factory()->create(['training_center_id' => $centro->id]);
        $director->assignRole('director_grupo_investigacion');

        $grupo = GrupoInvestigacion::create([
            'training_center_id' => $centro->id,
            'creator_id' => $director->id,
            'director_id' => $director->id,
            'nombre' => 'Grupo GDI',
            'codigo' => 'G-GDI-001',
            'estado' => EstadoEnum::Activo,
        ]);

        $coInv = User::factory()->create([
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => $grupo->id,
        ]);
        $coInv->assignRole('co_investigador_gdi');

        return [$coInv, $centro, $grupo];
    }

    public function test_store_ignora_training_center_id_del_request_y_usa_el_del_usuario(): void
    {
        [$coInv, $centro] = $this->crearCoInvestigadorGdiConGrupo();
        $otroCentro = TrainingCenter::create([
            'nombre' => 'Otro Centro GDI', 'codigo' => 'GDI-002', 'activo' => true,
            'department_id' => $centro->department_id, 'city_id' => $centro->city_id,
        ]);
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea GDI']);

        $response = $this->actingAs($coInv)->post(route('co-investigador-gdi.productos.store'), [
            'nombre' => 'Producto con intento de override',
            'research_line_id' => $linea->id,
            // El formulario real no ofrece este campo; si de todas formas
            // llega en el request (ej. manipulado), debe ser ignorado.
            'training_center_id' => $otroCentro->id,
        ]);

        $producto = MincienciasProduct::first();
        $response->assertRedirect(route('co-investigador-gdi.productos.show', $producto));
        $this->assertSame($centro->id, $producto->training_center_id);
        $this->assertNotSame($otroCentro->id, $producto->training_center_id);
    }

    public function test_producto_hereda_el_grupo_de_investigacion_del_creador(): void
    {
        [$coInv, $centro, $grupo] = $this->crearCoInvestigadorGdiConGrupo();
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea GDI 2']);

        $this->actingAs($coInv)->post(route('co-investigador-gdi.productos.store'), [
            'nombre' => 'Producto heredado',
            'research_line_id' => $linea->id,
        ]);

        $producto = MincienciasProduct::first();
        $this->assertSame($grupo->id, $producto->grupo_investigacion_id);
    }

    public function test_formulario_de_creacion_no_ofrece_selector_de_centro(): void
    {
        [$coInv] = $this->crearCoInvestigadorGdiConGrupo();

        $response = $this->actingAs($coInv)->get(route('co-investigador-gdi.productos.create'));

        $response->assertOk();
        $response->assertDontSee('training_center_id', false);
    }
}
