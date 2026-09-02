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
 * BUG-20260813-056 — varios puntos del sistema navegaban al ROL PRINCIPAL
 * (o a permisos acumulados de TODOS los roles asignados) en vez de al ROL
 * ACTIVO, usando <a href> o redirect() directos que no pasan por
 * roles.switch. Para un usuario multi-rol cuyo rol activo era distinto de
 * su rol principal, esos enlaces terminaban en 403 (aislamiento de la
 * Fase 2). Encontrado al probar: cambiar de rol activo y luego volver
 * (logo SIGESI / ruta /dashboard genérica) daba 403.
 */
class BUG20260813056Test extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private TrainingCenter $centro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $depto = Department::create(['nombre' => 'Depto BUG-056']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-056', 'department_id' => $depto->id]);
        $this->centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-056', 'codigo' => 'B056', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $this->user = User::factory()->create(['training_center_id' => $this->centro->id, 'estado' => EstadoEnum::Activo]);
        $this->user->assignRole('director_semilleros');
        $this->user->assignRole('co_investigador');
    }

    public function test_logo_dashboard_url_apunta_al_rol_activo_no_al_principal(): void
    {
        // director_semilleros es el principal (mayor prioridad) -> activo por defecto.
        $this->actingAs($this->user)->get(route('dir-sem.dashboard'));

        // Cambia el rol activo al secundario.
        $this->post(route('roles.switch'), ['role' => 'co_investigador']);

        // Cualquier página compartida debe generar el link del logo apuntando
        // al rol ACTIVO (co_investigador), no al principal (director_semilleros).
        $response = $this->get(route('profile.edit'));
        $response->assertOk();
        $response->assertSee(route('co-investigador.dashboard'), false);
    }

    public function test_ruta_dashboard_generica_redirige_al_rol_activo_no_al_principal(): void
    {
        $this->actingAs($this->user)->get(route('dir-sem.dashboard'));
        $this->post(route('roles.switch'), ['role' => 'co_investigador']);

        // Antes del fix, /dashboard redirigía siempre a /director-semilleros
        // (rol principal) sin importar el rol activo, y eso daba 403.
        $response = $this->get(route('dashboard'));
        $response->assertRedirect('/co-investigador');
    }

    public function test_volver_al_rol_principal_desde_secundario_ya_no_da_403(): void
    {
        $this->actingAs($this->user)->get(route('dir-sem.dashboard'));
        $this->post(route('roles.switch'), ['role' => 'co_investigador']);

        // Clic en el logo o en "Dashboard" (ambos usan route('dashboard') o
        // el mismo $dashboardUrl) ya no debe romperse.
        $this->get(route('dashboard'))->assertRedirect('/co-investigador');
        $this->get('/co-investigador')->assertOk();
    }

    public function test_login_mount_revisitado_ya_autenticado_no_rompe_con_rol_activo_secundario(): void
    {
        $this->actingAs($this->user)->get(route('dir-sem.dashboard'));
        $this->post(route('roles.switch'), ['role' => 'co_investigador']);

        // Revisitar /login estando ya autenticado: el middleware "guest" de
        // Laravel intercepta antes de llegar a Login::mount() y manda a
        // /dashboard, que a su vez (ya corregido) redirige al rol ACTIVO.
        $response = $this->followingRedirects()->get(route('login'));
        $response->assertOk();
        $response->assertViewIs('co_investigador.dashboard');
    }

    public function test_enlace_director_semilleros_del_sidebar_para_rol_no_activo_usa_roles_switch(): void
    {
        // Un usuario cuyo rol activo es co_investigador pero que tiene
        // director_semilleros como adicional: el bloque @else del sidebar
        // mostraba antes un <a href> directo a dir-sem.dashboard (por el
        // permiso semilleros.listar, sin importar el rol activo).
        $this->actingAs($this->user)->get(route('dir-sem.dashboard'));
        $this->post(route('roles.switch'), ['role' => 'co_investigador']);

        $response = $this->get(route('co-investigador.dashboard'));
        $response->assertOk();
        // El enlace ahora es un formulario POST contra roles.switch con el
        // rol como valor, no un <a href> directo a dir-sem.dashboard.
        $response->assertDontSee('href="'.route('dir-sem.dashboard').'"', false);
        $response->assertSee('value="director_semilleros"', false);
    }
}
