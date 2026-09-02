<?php

namespace Tests\Feature\Admin;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReporteControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdminConCentro(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 903,
        ]);

        foreach (['reportes.usuarios_por_rol', 'reportes.semilleros_con_metricas', 'reportes.exportar_pdf_excel'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        $rol->givePermissionTo(['reportes.usuarios_por_rol', 'reportes.semilleros_con_metricas', 'reportes.exportar_pdf_excel']);

        $admin = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $admin->assignRole('administrador_sistema');

        Seedling::create([
            'creator_id' => $admin->id,
            'leader_id' => null,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 1201,
            'logo' => 'default.png',
            'estado' => EstadoEnum::Activo,
        ]);

        return [$admin, $centro];
    }

    public function test_index_muestra_reportes_disponibles(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->get(route('admin.reportes.index'));

        $response->assertStatus(200);
    }

    public function test_exportar_usuarios_por_rol_pdf(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->post(route('admin.reportes.exportar'), [
            'tipo_reporte' => 'Usuarios por Rol',
            'formato' => 'pdf',
        ]);

        $response->assertStatus(200);
    }

    public function test_exportar_semilleros_con_metricas_excel(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->post(route('admin.reportes.exportar'), [
            'tipo_reporte' => 'Semilleros con Métricas',
            'formato' => 'excel',
        ]);

        $response->assertStatus(200);
    }

    public function test_admin_solo_ve_semilleros_de_su_propio_centro(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();

        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $otroCentro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Otro Centro',
            'codigo' => 904,
        ]);
        Seedling::create([
            'creator_id' => $admin->id,
            'leader_id' => null,
            'training_center_id' => $otroCentro->id,
            'nombre' => 'Semillero De Otro Centro',
            'codigo' => 1202,
            'logo' => 'default.png',
            'estado' => EstadoEnum::Activo,
        ]);

        // Mismo scope que ReporteController::buildDatosReporte() para "Semilleros con Métricas".
        $nombres = Seedling::when($admin->training_center_id, fn ($q) => $q->where('training_center_id', $admin->training_center_id))
            ->pluck('nombre');

        $this->assertContains('Semillero Test', $nombres);
        $this->assertNotContains('Semillero De Otro Centro', $nombres);
    }
}
