<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\TrainingCenterAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * BUG-20260813-059 — scopeUserQueryForList() excluía a super_administrador
 * y al propio usuario que consulta, pero NO a otras cuentas
 * administrador_sistema. Antes de multi-rol nunca se notaba (solo hay un
 * admin por centro, y ese admin ya se excluía a sí mismo). Con multi-rol,
 * un administrador_sistema de OTRO centro puede "colarse" en el listado si
 * además tiene un rol global (ej. co_investigador) — se ve por ese rol
 * global sin importar el centro.
 */
class BUG20260813059Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);
    }

    private function crearCentro(string $codigo): TrainingCenter
    {
        $depto = Department::create(['nombre' => "Depto {$codigo}"]);
        $ciudad = City::create(['nombre' => "Ciudad {$codigo}", 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => "Centro {$codigo}", 'codigo' => $codigo, 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    public function test_administrador_de_otro_centro_con_rol_global_ya_no_aparece_en_el_listado(): void
    {
        $centroA = $this->crearCentro('B059A');
        $centroB = $this->crearCentro('B059B');

        $adminA = User::factory()->create(['training_center_id' => $centroA->id, 'estado' => EstadoEnum::Activo]);
        $adminA->assignRole('administrador_sistema');

        // Admin de OTRO centro, con co_investigador (rol global) como
        // secundario — antes del fix, esto lo hacía visible por el rol
        // global sin importar que administra otro centro.
        $adminB = User::factory()->create(['training_center_id' => $centroB->id, 'estado' => EstadoEnum::Activo]);
        $adminB->assignRole('administrador_sistema');
        $adminB->assignRole('co_investigador');

        $response = $this->actingAs($adminA)->get(route('admin.usuarios.index'));

        $response->assertOk();
        $response->assertDontSee($adminB->email);
    }

    public function test_scope_query_for_list_excluye_administrador_sistema_directamente(): void
    {
        $centroA = $this->crearCentro('B059C');
        $centroB = $this->crearCentro('B059D');

        $adminA = User::factory()->create(['training_center_id' => $centroA->id]);
        $adminA->assignRole('administrador_sistema');

        $adminB = User::factory()->create(['training_center_id' => $centroB->id]);
        $adminB->assignRole('administrador_sistema');
        $adminB->assignRole('co_investigador');

        $ids = TrainingCenterAccess::scopeUserQueryForList(User::query(), $adminA)->pluck('id')->all();

        $this->assertNotContains($adminB->id, $ids);
    }

    public function test_los_conteos_de_metricas_siguen_incluyendo_al_propio_admin(): void
    {
        // BUG-035 no debe romperse: scopeUserQueryForMetrics() (no tocado
        // por este fix) sigue contando al propio admin.
        $centro = $this->crearCentro('B059E');
        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');

        $count = TrainingCenterAccess::scopeUserQueryForMetrics(User::query(), $admin)->count();

        $this->assertSame(1, $count);
    }

    public function test_co_investigador_sin_ser_admin_sigue_visible_por_rol_global(): void
    {
        $centroA = $this->crearCentro('B059F');
        $centroB = $this->crearCentro('B059G');

        $adminA = User::factory()->create(['training_center_id' => $centroA->id]);
        $adminA->assignRole('administrador_sistema');

        $ciOtroCentro = User::factory()->create(['training_center_id' => null]);
        $ciOtroCentro->assignRole('co_investigador');

        $ids = TrainingCenterAccess::scopeUserQueryForList(User::query(), $adminA)->pluck('id')->all();

        $this->assertContains($ciOtroCentro->id, $ids);
    }
}
