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
 * Regresión: BUG-20260813-021
 * La página de detalle de proyecto del Co-investigador (BUG-020) solo
 * mostraba evidencias tipo "desarrollo". El co-investigador no puede subir
 * producto final (el controlador ya fuerza tipo=Desarrollo en el store),
 * pero sí debe poder VER en solo lectura el producto final que suba el
 * líder de proyecto, junto a su estado de revisión (aprobado/rechazado/
 * pendiente) en las dos etapas (líder de semillero y director de
 * semilleros) — igual que ya se muestra en el propio dashboard del líder
 * de proyecto.
 */
class BUG20260813021Test extends TestCase
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
            'codigo' => 923,
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
            'codigo' => 2405,
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

    public function test_coinvestigador_ve_producto_final_pendiente(): void
    {
        [$coinvestigador, $liderProyecto, $proyecto] = $this->crearEscenario();

        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Informe Final v1',
            'uploaded_by' => $liderProyecto->id,
            'estado_revision_lider' => EstadoRevisionEnum::Pendiente,
        ]);

        $response = $this->actingAs($coinvestigador)->get(route('co-investigador.proyectos.show', $proyecto));

        $response->assertOk();
        $response->assertSee('Producto Final');
        $response->assertSee('Informe Final v1');
        $response->assertSee('Pendiente');
    }

    public function test_coinvestigador_ve_producto_final_aprobado_por_director(): void
    {
        [$coinvestigador, $liderProyecto, $proyecto] = $this->crearEscenario();

        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Informe Final v2',
            'uploaded_by' => $liderProyecto->id,
            'estado_revision_lider' => EstadoRevisionEnum::Aprobado,
            'estado_revision_director' => EstadoRevisionEnum::Aprobado,
        ]);

        $response = $this->actingAs($coinvestigador)->get(route('co-investigador.proyectos.show', $proyecto));

        $response->assertOk();
        $response->assertSee('Informe Final v2');
        $response->assertSeeInOrder(['Informe Final v2', 'Aprobado']);
    }

    public function test_sin_producto_final_muestra_mensaje_vacio(): void
    {
        [$coinvestigador, , $proyecto] = $this->crearEscenario();

        $response = $this->actingAs($coinvestigador)->get(route('co-investigador.proyectos.show', $proyecto));

        $response->assertOk();
        $response->assertSee('El líder de proyecto aún no ha subido el producto final.');
    }

    public function test_formulario_de_evidencia_del_coinvestigador_no_ofrece_tipo_producto_final(): void
    {
        $contenido = file_get_contents(resource_path('views/co_investigador/proyectos/show.blade.php'));

        $this->assertStringNotContainsString('producto_final"', $contenido);
        $this->assertStringNotContainsString('name="tipo"', $contenido);
    }
}
