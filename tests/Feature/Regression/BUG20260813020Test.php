<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
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
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-020
 * Auditoría + refinamiento del rol Co-investigador:
 * 1) El sidebar tenía un ítem "Proyectos Vinculados" que duplicaba el
 *    "Dashboard" genérico (ambos apuntaban a co-investigador.dashboard).
 *    Se conservan ambos ítems pero con función distinta: "Dashboard" es el
 *    resumen + reporte, "Proyectos Vinculados" pasó a ser un desplegable
 *    (mismo patrón que el sidebar de Líder de Semillero) que lista cada
 *    proyecto individual.
 * 2) El dashboard ahora tiene 2 cards: resumen de proyectos vinculados y
 *    descarga de reporte (antes era una tabla con el botón de reporte en
 *    el header).
 * 3) Nueva página de detalle por proyecto (Coinvestigador\ProyectoController)
 *    — el co-investigador puede subir evidencias de tipo "desarrollo"
 *    ÚNICAMENTE (nunca producto_final, eso es exclusivo del líder de
 *    proyecto), ver los actores del proyecto (líder + aprendices) de solo
 *    lectura, y ve las evidencias que suba el líder de proyecto y
 *    viceversa (project_evidences no filtra por usuario, solo por
 *    proyecto). Cada quien solo puede eliminar lo que subió él mismo.
 *    Como un co-investigador puede estar vinculado a varios proyectos a la
 *    vez, cada acción valida la vinculación activa contra project_authors
 *    (ProyectoController::ensureVinculado()) en vez de asumir "mi proyecto"
 *    único como hace LiderProyectoContext.
 */
class BUG20260813020Test extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 922,
        ]);

        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $liderSemillero = User::factory()->create(['training_center_id' => $centro->id]);
        $liderSemillero->assignRole('lider_semillero');

        $liderProyecto = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $liderProyecto->assignRole('lider_proyecto');

        $coinvestigador = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $coinvestigador->assignRole('co_investigador');

        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 2404,
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

        $proyecto->authors()->attach($coinvestigador->id, ['activo' => true]);

        return [$coinvestigador, $liderProyecto, $proyecto];
    }

    public function test_sidebar_mantiene_dashboard_y_proyectos_vinculados_como_items_distintos(): void
    {
        [$coinvestigador] = $this->crearEscenario();

        $response = $this->actingAs($coinvestigador)->get(route('co-investigador.dashboard'));

        $response->assertOk();
        $html = $response->getContent();

        preg_match_all('/>\s*Dashboard\s*<\/a>/', $html, $dashboardMatches);
        $this->assertCount(1, $dashboardMatches[0]);
        $this->assertStringContainsString('Proyectos Vinculados', $html);
    }

    public function test_dashboard_muestra_card_de_resumen_y_card_de_reporte(): void
    {
        [$coinvestigador] = $this->crearEscenario();

        $response = $this->actingAs($coinvestigador)->get(route('co-investigador.dashboard'));

        $response->assertOk();
        $response->assertSee('Proyecto Test');
        $response->assertSee('Descargar reporte');
    }

    public function test_coinvestigador_no_vinculado_no_puede_ver_el_proyecto(): void
    {
        [, , $proyecto] = $this->crearEscenario();

        $otro = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $otro->assignRole('co_investigador');

        $response = $this->actingAs($otro)->get(route('co-investigador.proyectos.show', $proyecto));

        $response->assertForbidden();
    }

    public function test_coinvestigador_vinculado_ve_actores_y_evidencias_del_proyecto(): void
    {
        [$coinvestigador, $liderProyecto, $proyecto] = $this->crearEscenario();

        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::Desarrollo,
            'nombre' => 'Avance subido por el líder',
            'uploaded_by' => $liderProyecto->id,
        ]);

        $response = $this->actingAs($coinvestigador)->get(route('co-investigador.proyectos.show', $proyecto));

        $response->assertOk();
        $response->assertSee('Avance subido por el líder');
    }

    public function test_coinvestigador_solo_puede_subir_evidencia_tipo_desarrollo(): void
    {
        [$coinvestigador, , $proyecto] = $this->crearEscenario();

        $response = $this->actingAs($coinvestigador)->post(
            route('co-investigador.proyectos.evidencias.store', $proyecto),
            [
                'nombre' => 'Mi avance',
                'archivo' => UploadedFile::fake()->create('avance.pdf', 100),
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('project_evidences', [
            'project_id' => $proyecto->id,
            'nombre' => 'Mi avance',
            'tipo' => TipoEvidenciaEnum::Desarrollo->value,
            'uploaded_by' => $coinvestigador->id,
        ]);
    }

    public function test_coinvestigador_no_puede_eliminar_evidencia_ajena(): void
    {
        [$coinvestigador, $liderProyecto, $proyecto] = $this->crearEscenario();

        $evidenciaAjena = ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::Desarrollo,
            'nombre' => 'Avance del líder',
            'uploaded_by' => $liderProyecto->id,
        ]);

        $response = $this->actingAs($coinvestigador)->delete(route('co-investigador.evidencias.destroy', $evidenciaAjena));

        $response->assertForbidden();
        $this->assertDatabaseHas('project_evidences', ['id' => $evidenciaAjena->id]);
    }

    public function test_coinvestigador_puede_eliminar_su_propia_evidencia(): void
    {
        [$coinvestigador, , $proyecto] = $this->crearEscenario();

        $evidenciaPropia = ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::Desarrollo,
            'nombre' => 'Mi avance',
            'uploaded_by' => $coinvestigador->id,
        ]);

        $response = $this->actingAs($coinvestigador)->delete(route('co-investigador.evidencias.destroy', $evidenciaPropia));

        $response->assertRedirect();
        $this->assertDatabaseMissing('project_evidences', ['id' => $evidenciaPropia->id]);
    }
}
