<?php

namespace Tests\Feature\Regression;

use App\Models\InvestigationType;
use App\Models\Project;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-036
 * "Cada líder de proyecto lidera un solo proyecto" solo se validaba con
 * Rule::unique() en la aplicación, sin índice único en BD — dos peticiones
 * concurrentes podían pasar la validación antes de que la primera terminara
 * de guardar. Fix: índice único en projects.lider_proyecto_user_id.
 */
class BUG20260813036Test extends TestCase
{
    use RefreshDatabase;

    private function crearProyectoFixtures(): array
    {
        $depto = \App\Models\Department::firstOrCreate(['nombre' => 'Depto Test BUG-036']);
        $ciudad = \App\Models\City::firstOrCreate(['nombre' => 'Ciudad Test BUG-036', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro Test BUG-036', 'codigo' => 'BUG036', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $creador = User::factory()->create(['training_center_id' => $centro->id]);
        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id]);

        $semillero = Seedling::create([
            'creator_id' => $creador->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero BUG-036',
            'codigo' => random_int(1000, 999999),
            'logo' => '',
            'estado' => \App\Enums\EstadoEnum::Activo,
        ]);

        return [$semillero, $creador, $liderProyecto, [
            'research_line_id' => ResearchLine::firstOrCreate(['nombre' => 'Linea BUG-036'])->id,
            'technological_line_id' => TechnologicalLine::firstOrCreate(['nombre' => 'Linea Tec BUG-036'])->id,
            'thematic_area_id' => ThematicArea::firstOrCreate(['nombre' => 'Area BUG-036'])->id,
            'project_modality_id' => ProjectModality::firstOrCreate(['nombre' => 'Modalidad BUG-036'])->id,
            'investigation_type_id' => InvestigationType::firstOrCreate(['nombre' => 'Tipo BUG-036'])->id,
        ]];
    }

    public function test_base_de_datos_rechaza_dos_proyectos_con_el_mismo_lider_de_proyecto(): void
    {
        [$semillero, $creador, $liderProyecto, $catalogos] = $this->crearProyectoFixtures();

        Project::create(array_merge($catalogos, [
            'project_creator_id' => $creador->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'nombre' => 'Proyecto Uno',
            'fecha_inicio' => now(),
            'estado' => 'activo',
        ]));

        $this->expectException(QueryException::class);

        // Bypassea la validación de Laravel (como haría una segunda petición
        // concurrente que ya pasó Rule::unique antes de que la primera
        // terminara de guardar) — el índice único de BD es quien debe cortar.
        Project::create(array_merge($catalogos, [
            'project_creator_id' => $creador->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'nombre' => 'Proyecto Dos (duplicado)',
            'fecha_inicio' => now(),
            'estado' => 'activo',
        ]));
    }

    public function test_dos_proyectos_pueden_tener_lider_de_proyecto_nulo(): void
    {
        // El índice único no debe bloquear múltiples proyectos SIN líder
        // de proyecto asignado todavía (NULL no cuenta como duplicado).
        [$semillero, $creador, , $catalogos] = $this->crearProyectoFixtures();

        $p1 = Project::create(array_merge($catalogos, [
            'project_creator_id' => $creador->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => null,
            'nombre' => 'Proyecto Sin Lider Uno',
            'fecha_inicio' => now(),
            'estado' => 'activo',
        ]));

        $p2 = Project::create(array_merge($catalogos, [
            'project_creator_id' => $creador->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => null,
            'nombre' => 'Proyecto Sin Lider Dos',
            'fecha_inicio' => now(),
            'estado' => 'activo',
        ]));

        $this->assertNotNull($p1->id);
        $this->assertNotNull($p2->id);
    }
}
