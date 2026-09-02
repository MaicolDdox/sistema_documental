<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-026
 * El dashboard del Líder de Proyecto mostraba el estado de revisión del
 * producto final como 2 badges separados (líder de semillero / director),
 * obligando a interpretarlos en conjunto. Se agregó un estado combinado
 * explícito en un solo texto: aprobado-falta-director / rechazado-por-líder
 * / rechazado-por-director / aprobado-definitivo / pendiente.
 */
class BUG20260813026Test extends TestCase
{
    use RefreshDatabase;

    private function crearProyectoConEvidencia(array $estados): Project
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 928 + count($estados),
        ]);

        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);

        $liderSemillero = User::factory()->create(['training_center_id' => $centro->id]);
        $liderSemillero->assignRole('lider_semillero');

        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $liderProyecto->assignRole('lider_proyecto');

        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 4000 + count($estados),
            'logo' => '',
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

        ProjectEvidence::create(array_merge([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Producto Final v1',
            'uploaded_by' => $liderProyecto->id,
        ], $estados));

        $proyecto->setRelation('liderProyecto', $liderProyecto);

        return $proyecto;
    }

    public function test_traza_pendiente_cuando_nadie_ha_revisado(): void
    {
        $proyecto = $this->crearProyectoConEvidencia(['estado_revision_lider' => EstadoRevisionEnum::Pendiente]);

        $response = $this->actingAs($proyecto->liderProyecto)->get(route('lider-proyecto.dashboard'));

        $response->assertOk();
        $response->assertSee('Pendiente — ningún revisor lo ha aprobado todavía');
    }

    public function test_traza_aprobado_por_lider_falta_director(): void
    {
        $proyecto = $this->crearProyectoConEvidencia([
            'estado_revision_lider' => EstadoRevisionEnum::Aprobado,
            'estado_revision_director' => EstadoRevisionEnum::Pendiente,
        ]);

        $response = $this->actingAs($proyecto->liderProyecto)->get(route('lider-proyecto.dashboard'));

        $response->assertOk();
        $response->assertSee('Aprobado por el Líder de Semillero — falta revisión del Director de Semilleros');
    }

    public function test_traza_rechazado_por_lider(): void
    {
        $proyecto = $this->crearProyectoConEvidencia(['estado_revision_lider' => EstadoRevisionEnum::Rechazado]);

        $response = $this->actingAs($proyecto->liderProyecto)->get(route('lider-proyecto.dashboard'));

        $response->assertOk();
        $response->assertSee('Rechazado por el Líder de Semillero — corrige y vuelve a subir');
    }

    public function test_traza_rechazado_por_director(): void
    {
        $proyecto = $this->crearProyectoConEvidencia([
            'estado_revision_lider' => EstadoRevisionEnum::Aprobado,
            'estado_revision_director' => EstadoRevisionEnum::Rechazado,
        ]);

        $response = $this->actingAs($proyecto->liderProyecto)->get(route('lider-proyecto.dashboard'));

        $response->assertOk();
        $response->assertSee('Rechazado por el Director de Semilleros — corrige y vuelve a subir');
    }

    public function test_traza_aprobado_definitivo(): void
    {
        $proyecto = $this->crearProyectoConEvidencia([
            'estado_revision_lider' => EstadoRevisionEnum::Aprobado,
            'estado_revision_director' => EstadoRevisionEnum::Aprobado,
        ]);

        $response = $this->actingAs($proyecto->liderProyecto)->get(route('lider-proyecto.dashboard'));

        $response->assertOk();
        $response->assertSee('Aprobado definitivamente por el Director de Semilleros');
    }
}
