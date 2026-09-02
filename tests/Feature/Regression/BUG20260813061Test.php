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
 * BUG-20260813-061 — el checkbox de roles adicionales de BUG-060 se había
 * agregado a admin/usuarios/edit.blade.php (página de página completa), que
 * NADA en la interfaz enlaza. El lápiz de "Usuarios y Roles" en realidad
 * abre un modal Alpine.js completamente distinto, dentro de
 * admin/usuarios/index.blade.php, que comparte la misma ruta
 * admin.usuarios.update pero con su propio formulario en el DOM. El
 * checkbox se movió a ese modal real.
 */
class BUG20260813061Test extends TestCase
{
    use RefreshDatabase;

    public function test_el_listado_de_usuarios_incluye_el_checkbox_de_roles_adicionales_en_el_modal_real(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $depto = Department::create(['nombre' => 'Depto BUG-061']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-061', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-061', 'codigo' => 'B061', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $director = User::factory()->create(['training_center_id' => $centro->id, 'created_by_user_id' => $admin->id]);
        $director->assignRole('director_semilleros');
        Person::create([
            'user_id' => $director->id,
            'primer_nombre' => 'Director', 'primer_apellido' => 'BUG061',
            'genero' => 'prefiero no decirlo', 'celular' => 0, 'eps' => '',
            'email_institucional' => 'director-bug061@test.com',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.usuarios.index'));

        $response->assertOk();
        // El checkbox real vive dentro del modal Alpine, no en una página aparte.
        $response->assertSee('¿Este usuario tiene más roles?');
        $response->assertSee('rolesAdicionalesOpciones', false);
        $response->assertSee('additionalRoles', false);
        // El payload que abre el modal ahora incluye los roles adicionales del usuario.
        $response->assertSee('additionalRoles', false);
    }

    public function test_editar_desde_el_modal_real_sigue_guardando_roles_adicionales(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $depto = Department::create(['nombre' => 'Depto BUG-061b']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-061b', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-061b', 'codigo' => 'B061B', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $director = User::factory()->create(['training_center_id' => $centro->id, 'created_by_user_id' => $admin->id]);
        $director->assignRole('director_semilleros');
        Person::create([
            'user_id' => $director->id,
            'primer_nombre' => 'Director', 'primer_apellido' => 'BUG061b',
            'genero' => 'prefiero no decirlo', 'celular' => 0, 'eps' => '',
            'email_institucional' => 'director-bug061b@test.com',
        ]);

        // Mismo endpoint que usa el modal (:action="editFormAction" -> admin.usuarios.update).
        $response = $this->actingAs($admin)->put(route('admin.usuarios.update', $director->id), [
            'nombre' => 'Director', 'apellido' => 'BUG061b',
            'numero_documento' => (string) $director->numero_documento,
            'email' => $director->email,
            'rol' => 'director_semilleros',
            'tiene_mas_roles' => '1',
            'additional_roles' => ['co_investigador', 'lider_semillero'],
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $director->refresh();
        $this->assertTrue($director->hasRole('co_investigador'));
        $this->assertTrue($director->hasRole('lider_semillero'));
    }
}
