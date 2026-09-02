<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Services\LiderSemillero\ProyectoLiderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-016
 * ProyectoLiderService::crearProyecto() insertaba automáticamente al Líder
 * de Proyecto como fila en project_authors (comentario original: "el Líder
 * de Proyecto queda vinculado como autor/participante del proyecto"),
 * código heredado de antes del rediseño. Como project_authors ahora
 * significa EXCLUSIVAMENTE "co-investigadores vinculados" (ver
 * Project::authors()), el Líder de Proyecto aparecía como co-investigador
 * de su propio proyecto en todas las vistas construidas hoy (dashboard de
 * Director de Semilleros, card de detalle de proyecto en Director y en
 * Líder de Semillero, dashboard del propio Líder de Proyecto).
 * Corregido: se quitó el ProjectAuthor::create() automático — el vínculo
 * único válido de co-investigador es el que crea explícitamente
 * LiderProyecto\CoinvestigadorController::store().
 */
class BUG20260813016Test extends TestCase
{
    use RefreshDatabase;

    public function test_crear_proyecto_no_vincula_al_lider_de_proyecto_como_coinvestigador(): void
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 918,
        ]);

        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);
        $liderSemillero = User::factory()->create(['training_center_id' => $centro->id]);
        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id]);
        $liderProyecto->assignRole('lider_proyecto');

        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 2301,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);

        $this->actingAs($liderSemillero);

        $servicio = app(ProyectoLiderService::class);
        $proyecto = $servicio->crearProyecto([
            'nombre' => 'Proyecto Test',
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
        ], $semillero);

        $this->assertDatabaseMissing('project_authors', [
            'project_id' => $proyecto->id,
            'user_id' => $liderProyecto->id,
        ]);

        $this->assertSame(0, Project::find($proyecto->id)->authors()->wherePivot('activo', true)->count());
    }
}
