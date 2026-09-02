<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * BUG-20260813-051 — FEAT-20260830-001 (Fase 1): núcleo de "rol activo"
 * para usuarios multi-rol. El selector de roles ya existente en el header
 * ("Roles y Módulos" -> "Mis roles") pasa de ser enlaces directos a botones
 * que cambian el rol activo en sesión antes de redirigir. El sidebar
 * (menuContext) debe reflejar el rol activo, no solo la URL actual.
 */
class BUG20260813051Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);
    }

    private function crearUsuarioMultiRol(): User
    {
        $user = User::factory()->create(['training_center_id' => null]);
        $user->assignRole('co_investigador');
        $user->assignRole('lider_proyecto');

        return $user;
    }

    public function test_usuario_con_un_solo_rol_no_ve_selector_de_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('lider_proyecto');

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertDontSee('Mis roles');
    }

    public function test_login_inicializa_el_rol_activo_al_rol_principal(): void
    {
        $user = $this->crearUsuarioMultiRol();

        // lider_proyecto tiene mayor prioridad que co_investigador
        // (RoleModuleLinks::LOGIN_ROLE_PRIORITY), así que es el principal.
        $this->actingAs($user)->get(route('profile.edit'));

        $this->assertSame('lider_proyecto', session('rol_activo'));
    }

    public function test_cambiar_a_un_rol_no_asignado_devuelve_403(): void
    {
        $user = $this->crearUsuarioMultiRol();

        $response = $this->actingAs($user)->post(route('roles.switch'), [
            'role' => 'director_semilleros',
        ]);

        $response->assertForbidden();
    }

    public function test_cambiar_de_rol_actualiza_la_sesion_y_redirige_al_dashboard_correcto(): void
    {
        $user = $this->crearUsuarioMultiRol();
        $this->actingAs($user)->get(route('profile.edit'));

        // El principal (lider_proyecto) queda activo por defecto; se cambia
        // al secundario (co_investigador) para probar una transición real.
        $response = $this->post(route('roles.switch'), [
            'role' => 'co_investigador',
        ]);

        $response->assertRedirect('/co-investigador');
        $this->assertSame('co_investigador', session('rol_activo'));
    }

    public function test_el_sidebar_refleja_el_rol_activo_tras_cambiar_en_una_pagina_compartida(): void
    {
        $user = $this->crearUsuarioMultiRol();
        $this->actingAs($user)->get(route('profile.edit'));

        // Cambia el rol activo al secundario (co_investigador).
        $this->post(route('roles.switch'), ['role' => 'co_investigador']);

        // /settings/profile no tiene prefijo de rol propio: antes del fix el
        // sidebar seguía mostrando el contexto del rol principal aquí
        // porque no lo derivaba del rol activo, solo de la URL.
        $response = $this->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Activo ahora', false);
    }
}
