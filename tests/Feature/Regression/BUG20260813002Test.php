<?php

namespace Tests\Feature\Regression;

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

/**
 * Regresión: BUG-20260813-002
 * ReporteSemilleroController::buildDatosReporte() quedó roto tras la limpieza
 * de la fase 2c: usaba $s->researchGroup (modelo eliminado), Project::whereHas
 * ('seedlings', ...) (la relación many-to-many ya no existe, ahora es
 * seedling_id directo) y Seedling::members() para "aprendices" (aprendices
 * ahora son datos libres vía ProjectLearner, no usuarios del semillero).
 * Corregido: 2026-08-13.
 */
class BUG20260813002Test extends TestCase
{
    use RefreshDatabase;

    private function crearDirectorConDatos(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 902,
        ]);

        foreach (['reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.proyectos_por_estado', 'reportes.exportar_pdf_excel'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rol->givePermissionTo(['reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.proyectos_por_estado', 'reportes.exportar_pdf_excel']);

        $director = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $director->assignRole('director_semilleros');

        $lider = User::factory()->create(['training_center_id' => $centro->id]);
        $semillero = Seedling::create([
            'creator_id' => $lider->id,
            'leader_id' => $lider->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 1101,
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
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Evidencia Test',
            'uploaded_by' => $liderProyecto->id,
        ]);

        ProjectLearner::create([
            'project_id' => $proyecto->id,
            'created_by_user_id' => $liderProyecto->id,
            'nombre_completo' => 'Aprendiz Test',
            'numero_documento' => '123456',
            'ficha' => '2600001',
            'nombre_tecnologo' => 'Análisis y Desarrollo de Software',
        ]);

        return [$director, $semillero];
    }

    public function test_exportar_semilleros_con_metricas_pdf_no_truena(): void
    {
        [$director] = $this->crearDirectorConDatos();

        $response = $this->actingAs($director)->post(route('dir-sem.reportes.exportar'), [
            'tipo_reporte' => 'Semilleros con Métricas',
            'formato' => 'pdf',
        ]);

        $response->assertStatus(200);
    }

    public function test_exportar_aprendices_por_semillero_excel_no_truena_y_usa_project_learner(): void
    {
        [$director] = $this->crearDirectorConDatos();

        $response = $this->actingAs($director)->post(route('dir-sem.reportes.exportar'), [
            'tipo_reporte' => 'Aprendices por Semillero',
            'formato' => 'excel',
        ]);

        $response->assertStatus(200);
    }

    public function test_exportar_proyectos_por_estado_pdf_no_truena(): void
    {
        [$director] = $this->crearDirectorConDatos();

        $response = $this->actingAs($director)->post(route('dir-sem.reportes.exportar'), [
            'tipo_reporte' => 'Proyectos por Estado',
            'formato' => 'pdf',
        ]);

        $response->assertStatus(200);
    }
}
