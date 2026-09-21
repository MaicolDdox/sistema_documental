<?php

namespace Tests\Feature\Web;

use App\Models\City;
use App\Models\Department;
use App\Models\EntityPosition;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reforma de catálogos por centro (2026-09-10): los 11 catálogos "puros"
 * (entity_positions, linkage_types, training_program_types, training_programs,
 * research_lines, technological_lines, thematic_areas, project_modalities,
 * investigation_types, minciencias_typologies, minciencias_subcategories)
 * quedan aislados por training_center_id, y solo administrador_sistema los
 * gestiona. Se prueba con entity_positions (tiene unique compuesto) y
 * research_lines (sin unique) como representativos de los 11.
 */
class CatalogTrainingCenterScopingTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdminConCentro(string $sufijo): array
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => "Depto CAT{$sufijo}"]);
        $ciudad = City::create(['nombre' => "Ciudad CAT{$sufijo}", 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => "Centro CAT{$sufijo}", 'codigo' => "CAT{$sufijo}", 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');

        return [$admin, $centro];
    }

    public function test_admin_no_ve_entity_positions_de_otro_centro_en_el_index(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro('A');
        [, $otroCentro] = $this->crearAdminConCentro('B');

        $propia = EntityPosition::create(['training_center_id' => $centro->id, 'nombre' => 'Docente Propio', 'descripcion' => '—']);
        $ajena = EntityPosition::create(['training_center_id' => $otroCentro->id, 'nombre' => 'Docente Ajeno', 'descripcion' => '—']);

        $response = $this->actingAs($admin)->get(route('admin.entity-positions.index'));

        $response->assertOk();
        $response->assertSee($propia->nombre);
        $response->assertDontSee($ajena->nombre);
    }

    public function test_admin_no_puede_editar_entity_position_de_otro_centro(): void
    {
        [$admin] = $this->crearAdminConCentro('C');
        [, $otroCentro] = $this->crearAdminConCentro('D');

        $ajena = EntityPosition::create(['training_center_id' => $otroCentro->id, 'nombre' => 'Docente Ajeno D', 'descripcion' => '—']);

        $this->actingAs($admin)->get(route('admin.entity-positions.edit', $ajena))->assertForbidden();
        $this->actingAs($admin)->put(route('admin.entity-positions.update', $ajena), [
            'nombre' => 'Hackeado', 'descripcion' => 'x',
        ])->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.entity-positions.destroy', $ajena))->assertForbidden();

        $this->assertSame('Docente Ajeno D', $ajena->fresh()->nombre);
    }

    public function test_store_ignora_training_center_id_del_request_y_usa_el_del_actor(): void
    {
        // NOTA: admin.research-lines.store devuelve 403 para TODOS incluso
        // antes de esta reforma — StoreResearchLineRequest::authorize()
        // retorna `false` (bug preexistente, no relacionado con el aislamiento
        // por centro; StoreCityRequest/StoreDepartmentRequest tienen el mismo
        // problema). Se usa entity-positions.store, cuyo FormRequest sí
        // autoriza, para probar la misma lógica de scoping en el controller.
        [$admin, $centro] = $this->crearAdminConCentro('E');
        [, $otroCentro] = $this->crearAdminConCentro('F');

        $this->actingAs($admin)->post(route('admin.entity-positions.store'), [
            'nombre' => 'Cargo Inyectado',
            'descripcion' => 'x',
            'training_center_id' => $otroCentro->id,
        ]);

        $cargo = EntityPosition::where('nombre', 'Cargo Inyectado')->firstOrFail();
        $this->assertSame($centro->id, $cargo->training_center_id);
    }

    public function test_dos_centros_pueden_tener_entity_position_con_el_mismo_nombre(): void
    {
        [$adminA, $centroA] = $this->crearAdminConCentro('G');
        [$adminB, $centroB] = $this->crearAdminConCentro('H');

        $this->actingAs($adminA)->post(route('admin.entity-positions.store'), [
            'nombre' => 'Docente', 'descripcion' => 'x',
        ])->assertRedirect(route('admin.entity-positions.index'));

        $this->actingAs($adminB)->post(route('admin.entity-positions.store'), [
            'nombre' => 'Docente', 'descripcion' => 'x',
        ])->assertRedirect(route('admin.entity-positions.index'));

        $this->assertDatabaseHas('entity_positions', ['nombre' => 'Docente', 'training_center_id' => $centroA->id]);
        $this->assertDatabaseHas('entity_positions', ['nombre' => 'Docente', 'training_center_id' => $centroB->id]);
    }

    public function test_lider_proyecto_recibe_403_en_rutas_de_catalogo(): void
    {
        [, $centro] = $this->crearAdminConCentro('I');

        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id]);
        $liderProyecto->assignRole('lider_proyecto');

        $this->actingAs($liderProyecto)->get(route('admin.entity-positions.index'))->assertForbidden();
        $this->actingAs($liderProyecto)->get(route('admin.research-lines.index'))->assertForbidden();
    }
}
