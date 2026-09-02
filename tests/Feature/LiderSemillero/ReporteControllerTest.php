<?php

namespace Tests\Feature\LiderSemillero;

use App\Enums\EstadoEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\ProjectLearner;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReporteControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearLiderConProyecto(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 905,
        ]);

        foreach (['reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.exportar_pdf_excel'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        $rol->givePermissionTo(['reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.exportar_pdf_excel']);

        $lider = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $lider->assignRole('lider_semillero');

        $semillero = Seedling::create([
            'creator_id' => $lider->id,
            'leader_id' => $lider->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 1301,
            'logo' => 'default.png',
            'estado' => EstadoEnum::Activo,
        ]);

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);
        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id]);
        $proyecto = Project::create([
            'project_creator_id' => $lider->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Test',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::Desarrollo,
            'nombre' => 'Avance Test',
            'uploaded_by' => $liderProyecto->id,
        ]);

        ProjectLearner::create([
            'project_id' => $proyecto->id,
            'created_by_user_id' => $liderProyecto->id,
            'nombre_completo' => 'Aprendiz Test',
            'numero_documento' => '111222',
            'ficha' => '2600002',
            'nombre_tecnologo' => 'Análisis y Desarrollo de Software',
        ]);

        return [$lider, $semillero];
    }

    public function test_index_muestra_reportes_disponibles(): void
    {
        [$lider] = $this->crearLiderConProyecto();

        $response = $this->actingAs($lider)->get(route('lider-sem.reportes.index'));

        $response->assertStatus(200);
    }

    public function test_exportar_proyectos_del_semillero_pdf(): void
    {
        [$lider] = $this->crearLiderConProyecto();

        $response = $this->actingAs($lider)->post(route('lider-sem.reportes.exportar'), [
            'tipo_reporte' => 'Proyectos del Semillero',
            'formato' => 'pdf',
        ]);

        $response->assertStatus(200);
    }

    public function test_exportar_aprendices_por_semillero_excel(): void
    {
        [$lider] = $this->crearLiderConProyecto();

        $response = $this->actingAs($lider)->post(route('lider-sem.reportes.exportar'), [
            'tipo_reporte' => 'Aprendices por Semillero',
            'formato' => 'excel',
        ]);

        $response->assertStatus(200);
    }

    public function test_lider_sin_semillero_recibe_404_al_exportar(): void
    {
        [$lider] = $this->crearLiderConProyecto();
        $lider->fresh()->ledSeedlings()->update(['leader_id' => null]);

        $response = $this->actingAs($lider)->post(route('lider-sem.reportes.exportar'), [
            'tipo_reporte' => 'Proyectos del Semillero',
            'formato' => 'pdf',
        ]);

        $response->assertStatus(404);
    }
}
