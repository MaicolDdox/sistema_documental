<?php

namespace Tests\Feature\LiderProyecto;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectAuthor;
use App\Models\ProjectEvidence;
use App\Models\ProjectLearner;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReporteControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearLiderProyectoConProyecto(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 906,
        ]);

        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);

        $liderProyecto = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $liderProyecto->assignRole('lider_proyecto');

        $liderSemillero = User::factory()->create(['training_center_id' => $centro->id]);
        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 1401,
            'logo' => 'default.png',
            'estado' => EstadoEnum::Activo,
        ]);

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);
        $proyecto = Project::create([
            'project_creator_id' => $liderSemillero->id,
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
        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Producto Test',
            'uploaded_by' => $liderProyecto->id,
            'estado_revision_lider' => EstadoRevisionEnum::Pendiente,
        ]);

        ProjectLearner::create([
            'project_id' => $proyecto->id,
            'created_by_user_id' => $liderProyecto->id,
            'nombre_completo' => 'Aprendiz Test',
            'numero_documento' => '333444',
            'ficha' => '2600003',
            'nombre_tecnologo' => 'Análisis y Desarrollo de Software',
        ]);

        $coinvestigador = User::factory()->create(['training_center_id' => null]);
        ProjectAuthor::create([
            'project_id' => $proyecto->id,
            'user_id' => $coinvestigador->id,
            'activo' => true,
        ]);

        return [$liderProyecto, $proyecto];
    }

    public function test_descargar_reporte_de_mi_proyecto_no_truena(): void
    {
        [$liderProyecto] = $this->crearLiderProyectoConProyecto();

        $response = $this->actingAs($liderProyecto)->get(route('lider-proyecto.reporte.descargar'));

        $response->assertStatus(200);
    }

    public function test_dashboard_muestra_boton_de_descargar_reporte(): void
    {
        [$liderProyecto] = $this->crearLiderProyectoConProyecto();

        $response = $this->actingAs($liderProyecto)->get(route('lider-proyecto.dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('lider-proyecto.reporte.descargar'), false);
    }
}
