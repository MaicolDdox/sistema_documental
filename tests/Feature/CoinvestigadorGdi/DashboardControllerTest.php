<?php

namespace Tests\Feature\CoinvestigadorGdi;

use App\Models\City;
use App\Models\Department;
use App\Models\GrupoInvestigacion;
use App\Models\Person;
use App\Models\ResearchLine;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-20260922-067 — El dashboard de co_investigador_gdi solo mostraba
 * conteos de productos Minciencias, sin decir a qué grupo de investigación
 * pertenecía el usuario. Se agrega una tarjeta de resumen del grupo.
 */
class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(): TrainingCenter
    {
        $depto = Department::create(['nombre' => 'Depto BUG-067']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-067', 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => 'Centro BUG-067', 'codigo' => 'B067', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    public function test_dashboard_muestra_resumen_del_grupo_de_investigacion(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro();

        $director = User::factory()->create(['training_center_id' => $centro->id]);
        Person::create([
            'user_id' => $director->id, 'primer_nombre' => 'Carlos', 'primer_apellido' => 'Ramírez',
            'genero' => 'masculino', 'celular' => 0, 'eps' => '',
        ]);

        $grupo = GrupoInvestigacion::create([
            'training_center_id' => $centro->id,
            'creator_id' => $director->id,
            'director_id' => $director->id,
            'nombre' => 'Grupo de Biotecnología',
            'codigo' => 'GB001',
            'descripcion' => 'Investigación en biotecnología aplicada.',
            'estado' => 'activo',
        ]);

        $linea = ResearchLine::create([
            'training_center_id' => $centro->id,
            'nombre' => 'Biotecnología Aplicada',
            'descripcion' => 'Línea de prueba',
        ]);
        $grupo->lineasInvestigacion()->attach($linea->id);

        $coInvestigador = User::factory()->create([
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => $grupo->id,
        ]);
        $coInvestigador->assignRole('co_investigador_gdi');

        $response = $this->actingAs($coInvestigador)->get(route('co-investigador-gdi.dashboard'));

        $response->assertOk();
        $response->assertSee('Grupo de Biotecnología');
        $response->assertSee('GB001');
        $response->assertSee('Carlos');
        $response->assertSee('Ramírez');
        $response->assertSee('Biotecnología Aplicada');
        $response->assertSee('Investigación en biotecnología aplicada.');
    }

    public function test_dashboard_sin_grupo_muestra_aviso(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro();

        $coInvestigador = User::factory()->create([
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => null,
        ]);
        $coInvestigador->assignRole('co_investigador_gdi');

        $response = $this->actingAs($coInvestigador)->get(route('co-investigador-gdi.dashboard'));

        $response->assertOk();
        $response->assertSee('No estás vinculado a ningún grupo de investigación');
    }
}
