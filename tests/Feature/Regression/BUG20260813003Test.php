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
 * Regresión: BUG-20260813-003
 * Reporte de bugs del panel de Administrador del Sistema:
 * 1. El usuario autenticado aparecía en sus propios listados (usuarios
 *    recientes, gestión de usuarios) — scopeUserQueryForList() ahora excluye
 *    siempre al usuario autenticado, no solo a super_administrador.
 * 2. El formulario de crear/editar usuario (modal en usuarios/index.blade.php)
 *    ofrecía CUALQUIER rol (incluido super_administrador) en vez de la matriz
 *    de creación exclusiva de administrador_sistema (director_semilleros,
 *    co_investigador).
 * 3. Se eliminaron las páginas "Asignar Roles" y "Usuarios con rol" (rutas,
 *    controlador, vistas) — funcionalidad redundante con la matriz de creación.
 * 4. El ítem de sidebar "Asesores Externos" se reemplazó por "Co-investigadores".
 * 5. Se eliminó el widget "Gestionar roles" por-fila (modal + rutas
 *    asignar-rol/revocar-rol + métodos del controlador) — ya redundante con
 *    la matriz de creación exclusiva y sin permiso asignado a ningún rol.
 * Corregido: 2026-08-13.
 *
 * NOTA (BUG-20260813-024): admin.usuarios.create/store ya no existen — la
 * creación se separó en admin.director-semilleros.store y
 * admin.co-investigadores.store, cada uno con el rol fijo en el controlador
 * (nunca leído del request). test_store_rechaza_rol_fuera_de_la_matriz_exclusiva
 * se actualizó para reflejar esto.
 */
class BUG20260813003Test extends TestCase
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
            'codigo' => 908,
        ]);

        foreach ([
            'usuarios.listar', 'usuarios.crear', 'usuarios.crear_director_semilleros', 'usuarios.crear_co_investigador',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        $rol->givePermissionTo(['usuarios.listar', 'usuarios.crear', 'usuarios.crear_director_semilleros', 'usuarios.crear_co_investigador']);
        Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);

        $admin = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $admin->assignRole('administrador_sistema');

        return [$admin, $centro];
    }

    public function test_usuario_autenticado_no_aparece_en_su_propio_listado(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $emails = TrainingCenterAccess::scopeUserQueryForList(User::query(), $admin)->pluck('email');

        $this->assertNotContains($admin->email, $emails);
    }

    public function test_pagina_gestion_usuarios_no_muestra_al_propio_admin(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->get(route('admin.usuarios.index'));

        $response->assertOk();
        // El email SÍ puede aparecer en el widget de cuenta del sidebar (quién
        // tiene la sesión abierta) — lo que no debe pasar es que el admin
        // aparezca como fila dentro de su propio listado de usuarios.
        $emailsEnListado = $response->viewData('usuarios')->getCollection()->pluck('email');
        $this->assertNotContains($admin->email, $emailsEnListado);
    }

    public function test_dashboard_no_muestra_acciones_rapidas(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Acciones Rápidas');
    }

    public function test_modal_crear_usuario_solo_ofrece_roles_de_la_matriz_exclusiva(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->get(route('admin.usuarios.index'));

        $response->assertOk();
        $nombresRolesAsignables = $response->viewData('rolesAsignables')->pluck('name');
        $this->assertEqualsCanonicalizing(['director_semilleros', 'co_investigador'], $nombresRolesAsignables->all());
    }

    public function test_store_con_rol_fijo_ignora_intento_de_override_por_request(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->post(route('admin.director-semilleros.store'), [
            'nombre' => 'Test',
            'apellido' => 'Usuario',
            'tipo_documento' => \App\Enums\TipoDocumentoEnum::CedulaCiudadana->value,
            'numero_documento' => '999888777',
            'email' => 'nuevo@test.com',
            'password' => 'Password123!',
            // El rol NO se lee del request en las rutas dedicadas — el
            // controlador lo fija en 'director_semilleros' sin importar esto.
            'rol' => 'super_administrador',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo@test.com']);
        $nuevo = User::where('email', 'nuevo@test.com')->firstOrFail();
        $this->assertTrue($nuevo->hasRole('director_semilleros'));
        $this->assertFalse($nuevo->hasRole('super_administrador'));
    }

    public function test_rutas_asignar_roles_y_usuarios_con_rol_ya_no_existen(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.usuarios.asignar_roles'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.usuarios.usuarios_con_rol'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.usuarios.asignar_rol_store'));
    }

    public function test_sidebar_muestra_coinvestigadores_no_asesores_externos(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Co-investigadores');
        $response->assertDontSee('Asesores Externos');
    }

    public function test_widget_gestionar_roles_por_fila_ya_no_existe(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.usuarios.asignar_rol'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.usuarios.revocar_rol'));

        $response = $this->actingAs($admin)->get(route('admin.usuarios.index'));

        $response->assertOk();
        $response->assertDontSee('Gestionar roles');
    }
}
