<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * BUG-20260813-055 — el dashboard de administrador_sistema tenía, desde
 * antes de esta sesión, una tarjeta "Accesos a otros módulos" con enlaces
 * <a href> directos a los otros roles del usuario. Desde la Fase 2 de
 * FEAT-20260830-001 (aislamiento por rol activo), esos enlaces navegaban
 * SIN pasar por roles.switch, así que el rol activo en sesión nunca se
 * actualizaba y el middleware active_role terminaba devolviendo 403 —
 * una regresión real sobre una funcionalidad que antes sí andaba.
 * Se eliminó la tarjeta duplicada a favor del único selector real
 * ("Mis roles" en el header, que sí pasa por roles.switch).
 */
class BUG20260813055Test extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_admin_ya_no_tiene_la_tarjeta_rota_de_accesos_a_otros_modulos(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $depto = Department::create(['nombre' => 'Depto BUG-055']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-055', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-055', 'codigo' => 'B055', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');
        $admin->assignRole('lider_semillero');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Accesos a otros módulos');
        // El único selector real de rol activo sigue disponible.
        $response->assertSee('Mis roles:', false);
    }

    public function test_ya_no_hay_ningun_enlace_directo_que_rompa_el_aislamiento_por_rol_activo(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $depto = Department::create(['nombre' => 'Depto BUG-055b']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-055b', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-055b', 'codigo' => 'B055B', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');
        $admin->assignRole('lider_semillero');

        $this->actingAs($admin)->get(route('admin.dashboard'));

        // El único camino para cambiar de rol es roles.switch (POST); ya no
        // debe quedar ningún <a href> directo a otro módulo en el dashboard.
        $content = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();
        $this->assertStringNotContainsString('href="http://localhost/lider-semillero"', $content);
        $this->assertStringNotContainsString("href='/lider-semillero'", $content);
    }
}
