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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-025
 * Nuevo ítem "Semilleros" para el rol Administrador del Sistema: vista de
 * solo lectura de los semilleros de su centro, con líder de semillero,
 * proyectos, líder de proyecto, integrantes (aprendices) y co-investigadores
 * vinculados. Sin edición ni eliminación — se mantiene la regla de que solo
 * quien crea un registro puede gestionarlo. Los permisos
 * 'semilleros.listar' / 'semilleros.ver_detalle' ya estaban seedeados para
 * administrador_sistema (RolesAndPermissionsSeeder) pero no tenían
 * controlador/vista/ruta implementados.
 */
class BUG20260813025Test extends TestCase
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
            'codigo' => 926,
        ]);
        $otroCentro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Otro Centro',
            'codigo' => 927,
        ]);

        foreach (['semilleros.listar', 'semilleros.ver_detalle'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rolAdmin = Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        $rolAdmin->givePermissionTo(['semilleros.listar', 'semilleros.ver_detalle']);
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $liderSemillero = User::factory()->create(['training_center_id' => $centro->id]);
        $liderSemillero->assignRole('lider_semillero');

        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $liderProyecto->assignRole('lider_proyecto');

        $coinvestigador = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $coinvestigador->assignRole('co_investigador');

        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Visible',
            'codigo' => 3001,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $semilleroOtroCentro = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $otroCentro->id,
            'nombre' => 'Semillero Ajeno',
            'codigo' => 3002,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);

        $proyecto = Project::create([
            'project_creator_id' => $liderSemillero->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Visible',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);
        $proyecto->authors()->attach($coinvestigador->id, ['activo' => true]);
        ProjectLearner::create([
            'project_id' => $proyecto->id,
            'created_by_user_id' => $liderProyecto->id,
            'nombre_completo' => 'Aprendiz Visible',
            'numero_documento' => '900111222',
            'ficha' => '2600001',
            'nombre_tecnologo' => 'ADSI',
        ]);

        return [$admin, $semillero, $semilleroOtroCentro, $liderSemillero, $liderProyecto, $coinvestigador];
    }

    public function test_admin_ve_solo_los_semilleros_de_su_centro(): void
    {
        [$admin, $semillero, $semilleroOtroCentro] = $this->crearEscenario();

        $response = $this->actingAs($admin)->get(route('admin.semilleros.index'));

        $response->assertOk();
        $response->assertSee('Semillero Visible');
        $response->assertDontSee('Semillero Ajeno');
    }

    public function test_detalle_muestra_lider_semillero_proyecto_lider_proyecto_aprendices_y_coinvestigadores(): void
    {
        [$admin, $semillero, , $liderSemillero, $liderProyecto, $coinvestigador] = $this->crearEscenario();

        $response = $this->actingAs($admin)->get(route('admin.semilleros.show', $semillero));

        $response->assertOk();
        $response->assertSee($liderSemillero->email);
        $response->assertSee('Proyecto Visible');
        $response->assertSee($liderProyecto->email);
        $response->assertSee('Aprendiz Visible');
        $response->assertSee($coinvestigador->email);
    }

    public function test_admin_no_puede_ver_detalle_de_semillero_de_otro_centro(): void
    {
        [$admin, , $semilleroOtroCentro] = $this->crearEscenario();

        $response = $this->actingAs($admin)->get(route('admin.semilleros.show', $semilleroOtroCentro));

        $response->assertForbidden();
    }

    public function test_vista_de_semilleros_no_ofrece_acciones_de_edicion_o_eliminacion(): void
    {
        [$admin, $semillero] = $this->crearEscenario();

        $response = $this->actingAs($admin)->get(route('admin.semilleros.show', $semillero));

        $response->assertOk();
        $response->assertDontSee('Eliminar');
        $response->assertDontSee('method="DELETE"', false);
    }
}
