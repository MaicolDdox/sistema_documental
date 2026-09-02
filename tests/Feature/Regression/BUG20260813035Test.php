<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\TrainingCenterAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-035
 * scopeUserQueryForList() excluye siempre al usuario que consulta (fix
 * deliberado de BUG-20260813-003 para listados de "gestionar otros
 * usuarios"). El problema: DashboardController (contador "Total Usuarios")
 * y ReporteController ("Usuarios por Rol") también lo usaban, y ahí el
 * propio admin SÍ es un usuario real de su centro — subcontaban en 1.
 * Fix: nuevo scopeUserQueryForMetrics() (sin exclusión) para esos 2 casos;
 * scopeUserQueryForList() sigue excluyendo donde corresponde (gestión de
 * usuarios, "usuarios recientes" — BUG-003 no se toca).
 */
class BUG20260813035Test extends TestCase
{
    use RefreshDatabase;

    private function crearAdminConCentro(): array
    {
        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-035']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-035', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro Test BUG-035', 'codigo' => 'BUG035', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        return [$admin, $centro];
    }

    public function test_scopeUserQueryForMetrics_incluye_al_propio_usuario(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $emails = TrainingCenterAccess::scopeUserQueryForMetrics(User::query(), $admin)->pluck('email');

        $this->assertContains($admin->email, $emails);
    }

    public function test_scopeUserQueryForList_sigue_excluyendo_al_propio_usuario(): void
    {
        // BUG-20260813-003 no se debe romper: gestión de usuarios sigue
        // sin mostrar al propio admin.
        [$admin] = $this->crearAdminConCentro();

        $emails = TrainingCenterAccess::scopeUserQueryForList(User::query(), $admin)->pluck('email');

        $this->assertNotContains($admin->email, $emails);
    }

    public function test_dashboard_total_usuarios_cuenta_al_propio_admin(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();
        $otro = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $otro->assignRole('director_semilleros');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        // 2 usuarios reales en el centro: el propio admin + el director.
        $this->assertSame(2, $response->viewData('totalUsuarios'));
    }

    public function test_dashboard_usuarios_recientes_sigue_sin_mostrar_al_propio_admin(): void
    {
        // Control: BUG-003 es intencional para este widget específico, no
        // debe revertirse por este fix.
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $emailsRecientes = $response->viewData('recentUsers')->pluck('email');
        $this->assertNotContains($admin->email, $emailsRecientes);
    }

    public function test_reporte_usuarios_por_rol_incluye_al_propio_admin(): void
    {
        [$admin] = $this->crearAdminConCentro();
        $admin->givePermissionTo(['reportes.usuarios_por_rol', 'reportes.exportar_pdf_excel']);

        $response = $this->actingAs($admin)->post(route('admin.reportes.exportar'), [
            'tipo_reporte' => 'Usuarios por Rol',
            'formato' => 'pdf',
        ]);

        $response->assertOk();
    }
}
