<?php

namespace Tests\Feature\Regression;

use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\TrainingCenterAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-031 / BUG-20260813-038
 * Un administrador_sistema podía existir sin training_center_id (no estaba
 * en CENTRO_BOUND_ROLE_NAMES). Eso producía dos síntomas del mismo estado
 * degenerado:
 * - scopeUserQueryForList() le mostraba SOLO co_investigadores.
 * - Si creaba un director_semilleros, ese director quedaba también sin
 *   centro, rompiendo la invariante en el otro sentido.
 * Fix: administrador_sistema se agregó a CENTRO_BOUND_ROLE_NAMES, y
 * UserCreationService::crearUsuario() (punto único de creación) rechaza
 * crear cualquier rol center-bound sin centro, en vez de dejarlo en manos
 * de cada controlador llamador.
 */
class BUG20260813031Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(): TrainingCenter
    {
        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-031']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-031', 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => 'Centro Test BUG-031', 'codigo' => 'BUG031', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    public function test_administrador_sistema_ahora_es_un_rol_atado_a_centro(): void
    {
        $this->assertContains('administrador_sistema', TrainingCenterAccess::CENTRO_BOUND_ROLE_NAMES);
        $this->assertTrue(TrainingCenterAccess::roleRequiresTrainingCenter('administrador_sistema'));
    }

    public function test_crear_administrador_sistema_sin_centro_lanza_validation_exception(): void
    {
        $service = app(\App\Services\Admin\UserCreationService::class);

        $this->expectException(ValidationException::class);

        $service->crearUsuario([
            'email' => 'admin-sin-centro@test.com',
            'numero_documento' => '999999999',
            'tipo_documento' => 'cedula ciudadana',
            'password' => 'password123',
            'primer_nombre' => 'Admin',
            'primer_apellido' => 'SinCentro',
            'rol' => 'administrador_sistema',
        ], null);
    }

    public function test_admin_sin_centro_ya_no_puede_crear_director_semilleros_sin_centro(): void
    {
        $service = app(\App\Services\Admin\UserCreationService::class);

        $this->expectException(ValidationException::class);

        // Simula exactamente la línea 138 de Admin/UsuarioController cuando
        // el actor (administrador_sistema) no tiene training_center_id.
        $service->crearUsuario([
            'email' => 'director-huerfano@test.com',
            'numero_documento' => '888888888',
            'tipo_documento' => 'cedula ciudadana',
            'password' => 'password123',
            'primer_nombre' => 'Director',
            'primer_apellido' => 'Huerfano',
            'rol' => 'director_semilleros',
        ], null);
    }

    public function test_crear_administrador_sistema_con_centro_funciona_normal(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro();
        $service = app(\App\Services\Admin\UserCreationService::class);

        $user = $service->crearUsuario([
            'email' => 'admin-con-centro@test.com',
            'numero_documento' => '777777777',
            'tipo_documento' => 'cedula ciudadana',
            'password' => 'password123',
            'primer_nombre' => 'Admin',
            'primer_apellido' => 'ConCentro',
            'rol' => 'administrador_sistema',
        ], $centro->id);

        $this->assertSame($centro->id, $user->training_center_id);
    }

    public function test_crear_co_investigador_sin_centro_sigue_funcionando(): void
    {
        // co_investigador queda fuera de CENTRO_BOUND_ROLE_NAMES a propósito:
        // este fix no debe exigirle centro.
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $service = app(\App\Services\Admin\UserCreationService::class);

        $user = $service->crearUsuario([
            'email' => 'coinv@test.com',
            'numero_documento' => '666666666',
            'tipo_documento' => 'cedula ciudadana',
            'password' => 'password123',
            'primer_nombre' => 'Co',
            'primer_apellido' => 'Investigador',
            'rol' => 'co_investigador',
        ], null);

        $this->assertNull($user->training_center_id);
    }
}
