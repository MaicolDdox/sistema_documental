<?php

namespace Tests\Feature\Coinvestigador;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectAuthor;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\RoleModuleLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearCoinvestigadorVinculado(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 907,
        ]);

        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        // co_investigador no tiene training_center_id (fuera de la jerarquía de semilleros).
        $coinvestigador = User::factory()->create([
            'training_center_id' => null,
            'estado' => EstadoEnum::Activo,
        ]);
        $coinvestigador->assignRole('co_investigador');

        $liderSemillero = User::factory()->create(['training_center_id' => $centro->id]);
        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 1501,
            'logo' => 'default.png',
            'estado' => EstadoEnum::Activo,
        ]);

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);
        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id]);
        $proyecto = Project::create([
            'project_creator_id' => $liderSemillero->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Test',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        ProjectAuthor::create([
            'project_id' => $proyecto->id,
            'user_id' => $coinvestigador->id,
            'activo' => true,
        ]);

        return [$coinvestigador, $proyecto];
    }

    public function test_dashboard_muestra_proyectos_vinculados(): void
    {
        [$coinvestigador, $proyecto] = $this->crearCoinvestigadorVinculado();

        $response = $this->actingAs($coinvestigador)->get(route('co-investigador.dashboard'));

        $response->assertStatus(200);
        $response->assertSee($proyecto->nombre);
    }

    public function test_reporte_descargar_no_truena(): void
    {
        [$coinvestigador] = $this->crearCoinvestigadorVinculado();

        $response = $this->actingAs($coinvestigador)->get(route('co-investigador.reporte.descargar'));

        $response->assertStatus(200);
    }

    public function test_role_module_links_apunta_al_dashboard_de_coinvestigador(): void
    {
        [$coinvestigador] = $this->crearCoinvestigadorVinculado();

        $this->assertEquals(
            route('co-investigador.dashboard'),
            RoleModuleLinks::dashboardUrlForUser($coinvestigador)
        );
    }

    public function test_otro_rol_no_puede_entrar_al_modulo_coinvestigador(): void
    {
        $otro = User::factory()->create(['estado' => EstadoEnum::Activo]);
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        $otro->assignRole('lider_semillero');

        $response = $this->actingAs($otro)->get(route('co-investigador.dashboard'));

        $response->assertStatus(403);
    }
}
