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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-019
 * El sidebar del Líder de Proyecto tenía dos enlaces apuntando a la misma
 * página: el "Dashboard" genérico de la sección "Principal" (resuelto por
 * RoleModuleLinks::dashboardUrlForUser(), que para este rol ya apunta a
 * lider-proyecto.dashboard) y el enlace "Resumen" de la sección "MI
 * PROYECTO", sobrante desde que BUG-20260813-018 fusionó Dashboard+Resumen
 * en una sola página. Se quitó el enlace "Resumen" duplicado y se agregó
 * 'lider-proyecto.dashboard' a $isDashboardActive para que el único enlace
 * "Dashboard" restante quede resaltado como activo en esa página.
 */
class BUG20260813019Test extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_no_repite_el_enlace_a_dashboard_de_lider_de_proyecto(): void
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 921,
        ]);

        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);

        $liderSemillero = User::factory()->create(['training_center_id' => $centro->id]);
        $liderSemillero->assignRole('lider_semillero');

        $liderProyecto = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $liderProyecto->assignRole('lider_proyecto');

        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 2403,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);

        Project::create([
            'project_creator_id' => $liderSemillero->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Test',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        $response = $this->actingAs($liderProyecto)->get(route('lider-proyecto.dashboard'));

        $response->assertOk();

        $html = $response->getContent();

        // El logo también enlaza al dashboard (comportamiento normal, no es un ítem
        // de navegación); lo que no debe repetirse es el ítem de menú "Dashboard".
        preg_match_all('/>\s*Dashboard\s*<\/a>/', $html, $matches);

        $this->assertCount(1, $matches[0], 'El sidebar solo debe tener un ítem de menú "Dashboard".');
        $this->assertStringNotContainsString('>Resumen</', $html);
    }
}
