<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * BUG-20260813-050 — el /dashboard genérico (y el middleware
 * RedirectDirectorToModule que se ejecuta antes) decidían a dónde
 * redirigir con hasRole() suelto, en un orden fijo que no coincidía con
 * la prioridad real de rol principal (RoleModuleLinks::LOGIN_ROLE_PRIORITY)
 * usada en el resto del sistema (login, sidebar, etc.). Un usuario con más
 * de un rol asignado caía siempre en la primera rama que matcheara sin
 * importar cuál fuera realmente su rol de mayor prioridad.
 */
class BUG20260813050Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_usuario_con_un_solo_rol_sigue_funcionando_igual(): void
    {
        $lider = User::factory()->create();
        $lider->assignRole('lider_semillero');

        $response = $this->actingAs($lider)->get('/dashboard');

        $response->assertRedirect('/lider-semillero');
    }

    public function test_super_administrador_tiene_prioridad_sobre_lider_semillero(): void
    {
        Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('lider_semillero');
        $user->assignRole('super_administrador');

        $response = $this->actingAs($user)->get('/dashboard');

        // Antes del fix esto redirigía a /lider-semillero (primera rama que
        // matcheaba), ignorando que super_administrador es de mayor prioridad.
        $response->assertRedirect('/super-admin/dashboard');
    }

    public function test_administrador_sistema_tiene_prioridad_sobre_director_semilleros(): void
    {
        $centro = \App\Models\TrainingCenter::create([
            'nombre' => 'Centro BUG-050',
            'codigo' => 'B050',
            'activo' => true,
            'department_id' => \App\Models\Department::create(['nombre' => 'Depto BUG-050'])->id,
            'city_id' => \App\Models\City::create(['nombre' => 'Ciudad BUG-050', 'department_id' => \App\Models\Department::first()->id])->id,
        ]);

        $user = User::factory()->create(['training_center_id' => $centro->id]);
        $user->assignRole('director_semilleros');
        $user->assignRole('administrador_sistema');

        // El middleware RedirectDirectorToModule corre antes que la ruta y
        // antes redirigía a /director-semilleros con solo hasRole() suelto,
        // en vez de renderizar el dashboard de administrador_sistema (que no
        // es una redirección sino un render inline del controlador admin).
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertStatus(200);
    }
}
