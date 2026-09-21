<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
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
 * Regresión: BUG-20260813-024
 * Reestructuración de "Gestión de Usuarios" del rol Administrador del
 * Sistema:
 * 1) "Usuarios" pasa a ser de solo lectura (filtrable por rol) — ya no
 *    ofrece crear usuarios; admin.usuarios.create/store se eliminaron.
 * 2) Nuevos ítems dedicados y exclusivos por rol: admin.director-semilleros
 *    (crea solo director_semilleros) y admin.co-investigadores (crea solo
 *    co_investigador) — el rol nunca se lee del request, se fija en el
 *    controlador, así ambos formularios quedan estrictamente separados.
 * 3) Bug de fondo encontrado durante la auditoría: co_investigador es un
 *    rol global (sin training_center_id, ver
 *    TrainingCenterAccess::CENTRO_BOUND_ROLE_NAMES), pero
 *    scopeUserQueryForList() filtraba TODOS los usuarios —incluido
 *    co_investigador— por el centro del actor, así que un administrador
 *    nunca veía ningún co-investigador en su listado. Corregido: los
 *    co_investigador se listan siempre, sin importar el centro del actor,
 *    además de los usuarios propios de su centro.
 */
class BUG20260813024Test extends TestCase
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
            'codigo' => 924,
        ]);
        $otroCentro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Otro Centro',
            'codigo' => 925,
        ]);

        foreach ([
            'usuarios.listar', 'usuarios.crear_director_semilleros', 'usuarios.crear_director_grupo_investigacion',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'administrador_sistema', 'guard_name' => 'web']);
        $rol->givePermissionTo(['usuarios.listar', 'usuarios.crear_director_semilleros', 'usuarios.crear_director_grupo_investigacion']);
        Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'director_grupo_investigacion', 'guard_name' => 'web']);

        $admin = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $admin->assignRole('administrador_sistema');

        return [$admin, $centro, $otroCentro];
    }

    public function test_rutas_de_creacion_generica_ya_no_existen(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.usuarios.create'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.usuarios.store'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.director-semilleros.create'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.director-semilleros.store'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.director-grupo-investigacion.create'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.director-grupo-investigacion.store'));
        // Reforma GDI/SDI: administrador_sistema ya no crea co-investigadores
        // directamente, esas rutas se eliminaron.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.co-investigadores.create'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.co-investigadores.store'));
    }

    public function test_pagina_usuarios_no_ofrece_boton_de_crear_generico(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->get(route('admin.usuarios.index'));

        $response->assertOk();
        $response->assertDontSee('Nuevo usuario');
        $response->assertSee('Director de Semilleros');
        $response->assertSee('Director de Grupo de Investigación');
    }

    public function test_crear_director_semilleros_asigna_ese_rol_y_hereda_centro_del_admin(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->post(route('admin.director-semilleros.store'), [
            'nombre' => 'Nuevo',
            'apellido' => 'Director',
            'tipo_documento' => TipoDocumentoEnum::CedulaCiudadana->value,
            'numero_documento' => '111222333',
            'email' => 'director.nuevo@test.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $creado = User::where('email', 'director.nuevo@test.com')->firstOrFail();
        $this->assertTrue($creado->hasRole('director_semilleros'));
        $this->assertFalse($creado->hasRole('director_grupo_investigacion'));
        $this->assertSame($centro->id, $creado->training_center_id);
    }

    /**
     * Reforma GDI/SDI: reemplaza test_crear_coinvestigador_asigna_ese_rol_sin_centro
     * (esa ruta y ese rol ya no existen). director_grupo_investigacion es el
     * rol nuevo que administrador_sistema crea en su lugar, y a diferencia
     * del co_investigador original, SÍ hereda el centro del admin.
     */
    public function test_crear_director_grupo_investigacion_asigna_ese_rol_y_hereda_centro_del_admin(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->post(route('admin.director-grupo-investigacion.store'), [
            'nombre' => 'Nuevo',
            'apellido' => 'DirectorGrupo',
            'tipo_documento' => TipoDocumentoEnum::CedulaCiudadana->value,
            'numero_documento' => '444555666',
            'email' => 'directorgrupo.nuevo@test.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $creado = User::where('email', 'directorgrupo.nuevo@test.com')->firstOrFail();
        $this->assertTrue($creado->hasRole('director_grupo_investigacion'));
        $this->assertFalse($creado->hasRole('director_semilleros'));
        $this->assertSame($centro->id, $creado->training_center_id);
    }

    /**
     * Reforma GDI/SDI: reemplaza test_admin_ve_coinvestigadores_de_todo_el_sistema_sin_filtro_de_centro.
     * Ya no existe ningún rol "global" — co_investigador (el que motivaba la
     * excepción sin filtro de centro) fue eliminado. Se confirma la
     * invariante que lo sustituye: TODOS los roles, sin excepción, quedan
     * acotados al centro del admin que consulta.
     */
    public function test_admin_no_ve_usuarios_de_otro_centro_al_no_existir_ya_roles_globales(): void
    {
        [$admin, $centro, $otroCentro] = $this->crearAdminConCentro();

        $directorGrupoOtroCentro = User::factory()->create(['training_center_id' => $otroCentro->id]);
        $directorGrupoOtroCentro->assignRole('director_grupo_investigacion');

        $usuarioOtroCentro = User::factory()->create(['training_center_id' => $otroCentro->id]);
        $usuarioOtroCentro->assignRole('director_semilleros');

        $usuarioMismoCentro = User::factory()->create(['training_center_id' => $centro->id]);
        $usuarioMismoCentro->assignRole('director_semilleros');

        $emails = TrainingCenterAccess::scopeUserQueryForList(User::query(), $admin)->pluck('email');

        $this->assertNotContains($directorGrupoOtroCentro->email, $emails);
        $this->assertContains($usuarioMismoCentro->email, $emails);
        $this->assertNotContains($usuarioOtroCentro->email, $emails);
    }
}
