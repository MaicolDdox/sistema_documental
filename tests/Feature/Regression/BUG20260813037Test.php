<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\InvestigationType;
use App\Models\Project;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-037
 * CoinvestigadorController::destroy() llamaba a updateExistingPivot() sin
 * verificar que existiera la fila del pivote para el proyecto del líder de
 * proyecto autenticado. Si el co-investigador nunca estuvo vinculado (o ya
 * estaba desvinculado), la llamada actualizaba 0 filas en silencio y el
 * controlador igual redirigía con "Co-investigador desvinculado" — un
 * mensaje de éxito falso.
 */
class BUG20260813037Test extends TestCase
{
    use RefreshDatabase;

    private function crearProyectoConLider(): array
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-037']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-037', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro Test BUG-037', 'codigo' => 'BUG037', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id]);
        $liderProyecto->assignRole('lider_proyecto');

        $semillero = Seedling::create([
            'creator_id' => $liderProyecto->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero BUG-037',
            'codigo' => random_int(1000, 999999),
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $proyecto = Project::create([
            'project_creator_id' => $liderProyecto->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => ResearchLine::firstOrCreate(['nombre' => 'Linea BUG-037'])->id,
            'technological_line_id' => TechnologicalLine::firstOrCreate(['nombre' => 'Linea Tec BUG-037'])->id,
            'thematic_area_id' => ThematicArea::firstOrCreate(['nombre' => 'Area BUG-037'])->id,
            'project_modality_id' => ProjectModality::firstOrCreate(['nombre' => 'Modalidad BUG-037'])->id,
            'investigation_type_id' => InvestigationType::firstOrCreate(['nombre' => 'Tipo BUG-037'])->id,
            'nombre' => 'Proyecto BUG-037',
            'fecha_inicio' => now(),
            'estado' => 'activo',
        ]);

        return [$liderProyecto, $proyecto];
    }

    public function test_desvincular_co_investigador_nunca_vinculado_reporta_error_no_exito(): void
    {
        [$liderProyecto, $proyecto] = $this->crearProyectoConLider();

        $coInvestigadorAjeno = User::factory()->create(['training_center_id' => null]);
        $coInvestigadorAjeno->assignRole('co_investigador');

        $response = $this->actingAs($liderProyecto)
            ->delete(route('lider-proyecto.coinvestigadores.destroy', $coInvestigadorAjeno));

        $response->assertRedirect(route('lider-proyecto.coinvestigadores.index'));
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');
    }

    public function test_desvincular_co_investigador_realmente_vinculado_sigue_funcionando(): void
    {
        [$liderProyecto, $proyecto] = $this->crearProyectoConLider();

        $coInvestigador = User::factory()->create(['training_center_id' => null]);
        $coInvestigador->assignRole('co_investigador');
        $proyecto->authors()->attach($coInvestigador->id, ['activo' => true]);

        $response = $this->actingAs($liderProyecto)
            ->delete(route('lider-proyecto.coinvestigadores.destroy', $coInvestigador));

        $response->assertRedirect(route('lider-proyecto.coinvestigadores.index'));
        $response->assertSessionHas('success');
        $response->assertSessionMissing('error');

        $this->assertDatabaseHas('project_authors', [
            'project_id' => $proyecto->id,
            'user_id' => $coInvestigador->id,
            'activo' => false,
        ]);
    }
}
