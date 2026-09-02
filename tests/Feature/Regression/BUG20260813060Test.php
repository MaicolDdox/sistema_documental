<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Person;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * BUG-20260813-060 — se agrega la gestión de "roles adicionales" (multi-rol)
 * también a las pantallas de EDITAR usuario, tanto de administrador_sistema
 * (admin.usuarios.edit/update, ya existente) como de super_administrador
 * (super-admin.administradores.edit/update y
 * super-admin.usuarios-sistema.edit/update, construidas desde cero — antes
 * de esto super_administrador no tenía NINGUNA forma de editar usuarios).
 */
class BUG20260813060Test extends TestCase
{
    use RefreshDatabase;

    private TrainingCenter $centro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $depto = Department::create(['nombre' => 'Depto BUG-060']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-060', 'department_id' => $depto->id]);
        $this->centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-060', 'codigo' => 'B060', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    private function crearPersona(User $user, string $nombre): void
    {
        Person::create([
            'user_id' => $user->id,
            'primer_nombre' => $nombre, 'primer_apellido' => 'BUG060',
            'genero' => 'prefiero no decirlo', 'celular' => 0, 'eps' => '',
            'email_institucional' => strtolower($nombre).'-bug060@test.com',
        ]);
    }

    public function test_administrador_sistema_agrega_roles_adicionales_al_editar_a_otro_usuario(): void
    {
        $admin = User::factory()->create(['training_center_id' => $this->centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $director = User::factory()->create(['training_center_id' => $this->centro->id, 'created_by_user_id' => $admin->id]);
        $director->assignRole('director_semilleros');
        $this->crearPersona($director, 'Director');

        $response = $this->actingAs($admin)->put(route('admin.usuarios.update', $director->id), [
            'nombre' => 'Director', 'apellido' => 'BUG060',
            'numero_documento' => (string) $director->numero_documento,
            'email' => $director->email,
            'rol' => 'director_semilleros',
            'tiene_mas_roles' => '1',
            'additional_roles' => ['co_investigador'],
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertTrue($director->fresh()->hasRole('co_investigador'));
    }

    public function test_administrador_sistema_no_puede_editarse_roles_adicionales_a_si_mismo(): void
    {
        $admin = User::factory()->create([
            'training_center_id' => $this->centro->id,
            'primary_role_name' => 'administrador_sistema',
            'estado' => EstadoEnum::Activo,
        ]);
        $admin->assignRole('administrador_sistema');
        $this->crearPersona($admin, 'Admin');

        $response = $this->actingAs($admin)->put(route('admin.usuarios.update', $admin->id), [
            'nombre' => 'Admin', 'apellido' => 'BUG060',
            'numero_documento' => (string) $admin->numero_documento,
            'email' => $admin->email,
            'rol' => 'administrador_sistema',
            'tiene_mas_roles' => '1',
            'additional_roles' => ['co_investigador'],
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertFalse($admin->fresh()->hasRole('co_investigador'));
    }

    public function test_super_administrador_edita_administrador_sistema_y_le_agrega_roles_adicionales(): void
    {
        $superAdmin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $superAdmin->assignRole('super_administrador');

        $admin = User::factory()->create(['training_center_id' => $this->centro->id]);
        $admin->assignRole('administrador_sistema');
        $this->crearPersona($admin, 'Admin');

        $response = $this->actingAs($superAdmin)->put(route('super-admin.administradores.update', $admin->id), [
            'nombre' => 'Admin', 'apellido' => 'BUG060',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => (string) $admin->numero_documento,
            'email' => $admin->email,
            'training_center_id' => $this->centro->id,
            'tiene_mas_roles' => '1',
            'additional_roles' => ['lider_semillero', 'co_investigador'],
        ]);

        $response->assertRedirect(route('super-admin.administradores.index'));
        $admin->refresh();
        $this->assertTrue($admin->hasRole('lider_semillero'));
        $this->assertTrue($admin->hasRole('co_investigador'));
        $this->assertTrue($admin->hasRole('administrador_sistema'));
    }

    public function test_super_administrador_no_puede_inyectar_super_administrador_editando_un_admin(): void
    {
        $superAdmin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $superAdmin->assignRole('super_administrador');

        $admin = User::factory()->create(['training_center_id' => $this->centro->id]);
        $admin->assignRole('administrador_sistema');
        $this->crearPersona($admin, 'Admin');

        $this->actingAs($superAdmin)->put(route('super-admin.administradores.update', $admin->id), [
            'nombre' => 'Admin', 'apellido' => 'BUG060',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => (string) $admin->numero_documento,
            'email' => $admin->email,
            'training_center_id' => $this->centro->id,
            'tiene_mas_roles' => '1',
            'additional_roles' => ['super_administrador'],
        ]);

        $this->assertFalse($admin->fresh()->hasRole('super_administrador'));
    }

    public function test_super_administrador_edita_usuario_del_sistema_y_le_agrega_roles_adicionales(): void
    {
        $superAdmin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $superAdmin->assignRole('super_administrador');

        $director = User::factory()->create(['training_center_id' => $this->centro->id]);
        $director->assignRole('director_semilleros');
        $this->crearPersona($director, 'Director');

        $response = $this->actingAs($superAdmin)->put(route('super-admin.usuarios-sistema.update', $director->id), [
            'nombre' => 'Director', 'apellido' => 'BUG060',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => (string) $director->numero_documento,
            'email' => $director->email,
            'rol' => 'director_semilleros',
            'training_center_id' => $this->centro->id,
            'tiene_mas_roles' => '1',
            'additional_roles' => ['co_investigador', 'lider_semillero'],
        ]);

        $response->assertRedirect(route('super-admin.usuarios-sistema.index'));
        $director->refresh();
        $this->assertTrue($director->hasRole('co_investigador'));
        $this->assertTrue($director->hasRole('lider_semillero'));
    }

    public function test_desmarcar_un_rol_adicional_en_edicion_lo_quita(): void
    {
        $superAdmin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $superAdmin->assignRole('super_administrador');

        $admin = User::factory()->create(['training_center_id' => $this->centro->id]);
        $admin->assignRole('administrador_sistema');
        $admin->assignRole('co_investigador');
        $this->crearPersona($admin, 'Admin');

        $this->actingAs($superAdmin)->put(route('super-admin.administradores.update', $admin->id), [
            'nombre' => 'Admin', 'apellido' => 'BUG060',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => (string) $admin->numero_documento,
            'email' => $admin->email,
            'training_center_id' => $this->centro->id,
            'tiene_mas_roles' => '0',
        ]);

        $this->assertFalse($admin->fresh()->hasRole('co_investigador'));
    }
}
