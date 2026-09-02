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
 * BUG-20260813-054 — FEAT-20260830-001 (corrección de alcance de la Fase 3):
 * los roles adicionales (multi-rol) solo se asignan al CREAR el usuario, no
 * al editarlo (BUG-053 quedó revertido: estaba en una pantalla de edición
 * que además no está enlazada en ningún lugar del sistema). El checkbox
 * "¿Este usuario tiene más roles?" vive en los 3 formularios reales de
 * creación: super_administrador creando administrador_sistema, y
 * administrador_sistema creando director_semilleros o co_investigador.
 */
class BUG20260813054Test extends TestCase
{
    use RefreshDatabase;

    private TrainingCenter $centro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $depto = Department::create(['nombre' => 'Depto BUG-054']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-054', 'department_id' => $depto->id]);
        $this->centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-054', 'codigo' => 'B054', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    public function test_super_administrador_crea_administrador_sistema_con_roles_adicionales(): void
    {
        $superAdmin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $superAdmin->assignRole('super_administrador');

        $response = $this->actingAs($superAdmin)->post(route('super-admin.administradores.store'), [
            'nombre' => 'Admin', 'apellido' => 'Multirol',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => '900000001',
            'email' => 'admin.multirol@test.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'training_center_id' => $this->centro->id,
            'tiene_mas_roles' => '1',
            'additional_roles' => ['lider_semillero', 'co_investigador'],
        ]);

        $response->assertRedirect(route('super-admin.administradores.index'));
        $creado = User::where('email', 'admin.multirol@test.com')->firstOrFail();
        $this->assertTrue($creado->hasRole('administrador_sistema'));
        $this->assertTrue($creado->hasRole('lider_semillero'));
        $this->assertTrue($creado->hasRole('co_investigador'));
        $this->assertSame($this->centro->id, $creado->training_center_id);
    }

    public function test_no_marcar_el_checkbox_ignora_additional_roles_aunque_lleguen_en_el_request(): void
    {
        $superAdmin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $superAdmin->assignRole('super_administrador');

        $this->actingAs($superAdmin)->post(route('super-admin.administradores.store'), [
            'nombre' => 'Admin', 'apellido' => 'SinExtra',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => '900000002',
            'email' => 'admin.sinextra@test.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'training_center_id' => $this->centro->id,
            // tiene_mas_roles NO enviado (checkbox sin marcar)
            'additional_roles' => ['co_investigador'],
        ]);

        $creado = User::where('email', 'admin.sinextra@test.com')->firstOrFail();
        $this->assertFalse($creado->hasRole('co_investigador'));
    }

    public function test_no_se_puede_inyectar_super_administrador_como_rol_adicional_al_crear(): void
    {
        $superAdmin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $superAdmin->assignRole('super_administrador');

        $this->actingAs($superAdmin)->post(route('super-admin.administradores.store'), [
            'nombre' => 'Admin', 'apellido' => 'Injection',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => '900000003',
            'email' => 'admin.injection@test.com',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'training_center_id' => $this->centro->id,
            'tiene_mas_roles' => '1',
            'additional_roles' => ['super_administrador'],
        ]);

        $creado = User::where('email', 'admin.injection@test.com')->firstOrFail();
        $this->assertFalse($creado->hasRole('super_administrador'));
    }

    public function test_administrador_sistema_crea_director_semilleros_con_co_investigador_adicional(): void
    {
        $admin = User::factory()->create(['training_center_id' => $this->centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $response = $this->actingAs($admin)->post(route('admin.director-semilleros.store'), [
            'nombre' => 'Director', 'apellido' => 'Multirol',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => '900000004',
            'email' => 'director.multirol@test.com',
            'password' => 'Password123!',
            'tiene_mas_roles' => '1',
            'additional_roles' => ['co_investigador'],
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $creado = User::where('email', 'director.multirol@test.com')->firstOrFail();
        $this->assertTrue($creado->hasRole('director_semilleros'));
        $this->assertTrue($creado->hasRole('co_investigador'));
        $this->assertSame($this->centro->id, $creado->training_center_id);
    }

    public function test_administrador_sistema_crea_co_investigador_con_lider_semillero_adicional_hereda_el_centro(): void
    {
        $admin = User::factory()->create(['training_center_id' => $this->centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $response = $this->actingAs($admin)->post(route('admin.co-investigadores.store'), [
            'nombre' => 'Coinvestigador', 'apellido' => 'ConLider',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => '900000005',
            'email' => 'ci.conlider@test.com',
            'password' => 'Password123!',
            'tiene_mas_roles' => '1',
            'additional_roles' => ['lider_semillero'],
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $creado = User::where('email', 'ci.conlider@test.com')->firstOrFail();
        $this->assertTrue($creado->hasRole('co_investigador'));
        $this->assertTrue($creado->hasRole('lider_semillero'));
        // Al tener un rol adicional que exige centro, hereda el del admin creador.
        $this->assertSame($this->centro->id, $creado->training_center_id);
    }

    public function test_co_investigador_puro_sin_roles_adicionales_sigue_sin_centro(): void
    {
        $admin = User::factory()->create(['training_center_id' => $this->centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $this->actingAs($admin)->post(route('admin.co-investigadores.store'), [
            'nombre' => 'Coinvestigador', 'apellido' => 'Puro',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => '900000006',
            'email' => 'ci.puro@test.com',
            'password' => 'Password123!',
        ]);

        $creado = User::where('email', 'ci.puro@test.com')->firstOrFail();
        $this->assertTrue($creado->hasRole('co_investigador'));
        $this->assertNull($creado->training_center_id);
    }
}
