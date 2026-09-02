<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectLearner;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-018
 * Refinamiento del rol Líder de Proyecto pedido explícitamente por el
 * usuario, en 4 partes:
 * 1) Dashboard y "Resumen" eran páginas duplicadas — se fusionaron en el
 *    dashboard: ahora muestra el resumen específico del proyecto, más un
 *    card de solo-lectura de Aprendices vinculados y otro de
 *    Co-investigadores vinculados (sin acciones).
 * 2) Evidencias: sin cambios (confirmado explícitamente por el usuario).
 * 3) Tabla de Aprendices: el botón "Guardar" pasó a ser un botón-ícono de
 *    "Editar" (lápiz) y "Eliminar" pasó a ícono de basura, en vez de texto.
 * 4) Co-investigadores: el listado de "disponibles" solo se poblaba si
 *    había un término de búsqueda (CoinvestigadorController::index()
 *    devolvía collect() vacío sin `search`). Ahora siempre trae todos los
 *    co-investigadores del sistema no vinculados al proyecto, y el buscador
 *    queda como filtro opcional adicional.
 */
class BUG20260813018Test extends TestCase
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
            'codigo' => 920,
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

        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 2402,
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

        return [$liderProyecto, $proyecto];
    }

    public function test_dashboard_muestra_resumen_aprendices_y_coinvestigadores_sin_variables_indefinidas(): void
    {
        [$liderProyecto, $proyecto] = $this->crearEscenario();

        ProjectLearner::create([
            'project_id' => $proyecto->id,
            'created_by_user_id' => $liderProyecto->id,
            'nombre_completo' => 'Aprendiz Uno',
            'numero_documento' => '1000000001',
            'ficha' => '2500001',
            'nombre_tecnologo' => 'Análisis y Desarrollo de Software',
        ]);

        $coinvestigador = User::factory()->create();
        $coinvestigador->assignRole('co_investigador');
        $proyecto->authors()->attach($coinvestigador->id, ['activo' => true]);

        $response = $this->actingAs($liderProyecto)->get(route('lider-proyecto.dashboard'));

        $response->assertOk();
        $response->assertSee('Resumen del Proyecto');
        $response->assertSee('Aprendiz Uno');
        $response->assertSee($coinvestigador->email);
    }

    public function test_tabla_de_aprendices_usa_boton_icono_editar_en_vez_de_texto_guardar(): void
    {
        $contenido = file_get_contents(resource_path('views/lider_proyecto/aprendices/index.blade.php'));

        $this->assertStringNotContainsString('>Guardar</button>', $contenido);
        $this->assertStringContainsString('title="Editar"', $contenido);
        $this->assertStringContainsString('title="Eliminar"', $contenido);
    }

    public function test_coinvestigadores_disponibles_se_listan_sin_necesidad_de_buscar(): void
    {
        [$liderProyecto, $proyecto] = $this->crearEscenario();

        $disponible = User::factory()->create(['email' => 'disponible@test.com']);
        $disponible->assignRole('co_investigador');

        $response = $this->actingAs($liderProyecto)->get(route('lider-proyecto.coinvestigadores.index'));

        $response->assertOk();
        $response->assertSee('disponible@test.com');
    }

    public function test_coinvestigadores_ya_vinculados_no_aparecen_en_disponibles(): void
    {
        [$liderProyecto, $proyecto] = $this->crearEscenario();

        $vinculado = User::factory()->create(['email' => 'vinculado@test.com']);
        $vinculado->assignRole('co_investigador');
        $proyecto->authors()->attach($vinculado->id, ['activo' => true]);

        $response = $this->actingAs($liderProyecto)->get(route('lider-proyecto.coinvestigadores.index'));

        $response->assertOk();
        $response->assertViewHas('disponibles', fn ($disponibles) => ! $disponibles->contains('id', $vinculado->id));
    }
}
