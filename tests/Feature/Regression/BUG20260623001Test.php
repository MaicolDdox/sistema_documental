<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\TrainingCenterAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260623-01
 * TrainingCenterAccess::scopeUserQueryForList() filtraba por training_center_id
 * pero nunca excluía a los usuarios con rol super_administrador. Un administrador
 * de sistema que compartiera training_center_id (o ambos con null) con un super
 * admin lo veía en su listado de usuarios y podía incluso cambiarle el estado.
 * Corregido: 2026-06-23 — whereDoesntHave('roles', 'super_administrador') cuando
 * el usuario autenticado no es super admin.
 *
 * El mismo defecto se repetía en Admin\DashboardController::index(), que
 * reimplementaba el scope de training_center_id a mano (sin pasar por
 * TrainingCenterAccess) para las métricas y el widget de usuarios recientes.
 * Corregido: 2026-06-23 — ambos consumen scopeUserQueryForList().
 */
class BUG20260623001Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(): TrainingCenter
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Regresion']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Regresion', 'department_id' => $dpto->id]);

        return TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Regresion',
            'codigo' => 777,
        ]);
    }

    private function crearAdminDeCentro(int $centroId): User
    {
        Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);

        $admin = User::factory()->create([
            'training_center_id' => $centroId,
            'estado' => EstadoEnum::Activo,
        ]);
        $admin->assignRole('administrador_sistema');

        return $admin;
    }

    public function test_super_admin_no_aparece_en_listado_de_admin_de_centro_aunque_compartan_centro(): void
    {
        Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);
        $centro = $this->crearCentro();

        $admin = $this->crearAdminDeCentro($centro->id);

        $superAdmin = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $superAdmin->assignRole('super_administrador');

        $otroDelCentro = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);

        $resultado = TrainingCenterAccess::scopeUserQueryForList(User::query(), $admin)
            ->pluck('email');

        $this->assertNotContains($superAdmin->email, $resultado, 'El super administrador nunca debe ser visible en el listado del admin de centro');
        $this->assertContains($otroDelCentro->email, $resultado, 'Un usuario normal del mismo centro sí debe ser visible');
    }

    public function test_super_admin_no_aparece_aunque_ambos_tengan_training_center_id_nulo(): void
    {
        Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);

        $admin = $this->crearAdminDeCentro($this->crearCentro()->id);
        $admin->training_center_id = null;
        $admin->save();

        $superAdmin = User::factory()->create([
            'training_center_id' => null,
            'estado' => EstadoEnum::Activo,
        ]);
        $superAdmin->assignRole('super_administrador');

        $resultado = TrainingCenterAccess::scopeUserQueryForList(User::query(), $admin)
            ->pluck('email');

        $this->assertNotContains($superAdmin->email, $resultado);
    }

    public function test_admin_no_ve_super_admin_en_listado_http_del_controlador(): void
    {
        Permission::firstOrCreate(['name' => 'usuarios.listar', 'guard_name' => 'web']);
        $rolAdmin = Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        $rolAdmin->givePermissionTo('usuarios.listar');
        Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);

        $centro = $this->crearCentro();
        $admin = $this->crearAdminDeCentro($centro->id);

        $superAdmin = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $superAdmin->assignRole('super_administrador');

        $response = $this->actingAs($admin)->get(route('admin.usuarios.index'));

        $response->assertOk();
        $response->assertDontSee($superAdmin->email);
    }

    public function test_dashboard_admin_no_cuenta_ni_lista_super_admin_del_mismo_centro(): void
    {
        Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);

        $centro = $this->crearCentro();
        $admin = $this->crearAdminDeCentro($centro->id);

        $superAdmin = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $superAdmin->assignRole('super_administrador');

        $otroDelCentro = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee($superAdmin->email);
        // Desde BUG-20260813-035, el contador "Total Usuarios" usa
        // scopeUserQueryForMetrics() (no scopeUserQueryForList()): el propio
        // admin SÍ cuenta como usuario real de su centro — solo el super
        // administrador queda excluido. Antes de ese fix subcontaba en 1.
        $this->assertEquals(2, $response->viewData('totalUsuarios'), 'totalUsuarios debe contar al propio admin y a otroDelCentro, pero no al super administrador');

        $recentEmails = $response->viewData('recentUsers')->pluck('email');
        $this->assertNotContains($superAdmin->email, $recentEmails);
        $this->assertContains($otroDelCentro->email, $recentEmails);
    }
}
