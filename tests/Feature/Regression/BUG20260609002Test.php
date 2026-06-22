<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Services\Admin\UserCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260609-002
 * La lógica de creación de usuario (User + Person + assignRole + primary_role_name)
 * estaba duplicada literalmente en 4 controladores distintos, sin transacción
 * centralizada. Un fallo a mitad del proceso podía dejar datos inconsistentes.
 * Corregido: 2026-06-09 — centralizado en UserCreationService::crearUsuario()
 * con DB::transaction atómica.
 */
class BUG20260609002Test extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_service_crea_user_y_person_atomicamente(): void
    {
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);

        $service = app(UserCreationService::class);

        $usuario = $service->crearUsuario([
            'email'            => 'test@regresion.com',
            'tipo_documento'   => TipoDocumentoEnum::CedulaCiudadana->value,
            'numero_documento' => '12345678',
            'password'         => 'secret123',
            'primer_nombre'    => 'Ana',
            'segundo_nombre'   => null,
            'primer_apellido'  => 'Torres',
            'segundo_apellido' => null,
            'rol'              => 'lider_semillero',
        ], trainingCenterId: null);

        $this->assertDatabaseHas('users', [
            'email'          => 'test@regresion.com',
            'numero_documento' => '12345678',
        ]);

        $this->assertDatabaseHas('people', [
            'primer_nombre'   => 'Ana',
            'primer_apellido' => 'Torres',
            'user_id'         => $usuario->id,
        ]);

        $this->assertTrue($usuario->hasRole('lider_semillero'), 'El usuario debe tener el rol asignado');
        $this->assertEquals('lider_semillero', $usuario->primary_role_name, 'primary_role_name debe ser el rol asignado');
    }

    public function test_user_creation_service_es_atomico_si_rol_invalido(): void
    {
        $service = app(UserCreationService::class);

        $conteoAntes = DB::table('users')->count();

        try {
            $service->crearUsuario([
                'email'            => 'fail@test.com',
                'tipo_documento'   => TipoDocumentoEnum::CedulaCiudadana->value,
                'numero_documento' => '99999999',
                'password'         => 'secret123',
                'primer_nombre'    => 'Fail',
                'primer_apellido'  => 'User',
                'rol'              => 'rol_que_no_existe',
            ], trainingCenterId: null);
        } catch (\Throwable) {
            // Se espera excepción
        }

        $this->assertEquals($conteoAntes, DB::table('users')->count(), 'No debe quedar un usuario huérfano si la transacción falla');
    }

    public function test_split_nombre_divide_correctamente(): void
    {
        $service = app(UserCreationService::class);

        // splitNombre devuelve array indexado [$primerToken, $resto]
        $resultado = $service->splitNombre('Juan Carlos');
        $this->assertEquals('Juan', $resultado[0]);
        $this->assertEquals('Carlos', $resultado[1]);

        $resultado2 = $service->splitNombre('Ana María López Gómez');
        $this->assertEquals('Ana', $resultado2[0]);
        $this->assertEquals('María López Gómez', $resultado2[1]);

        [$soloUno] = $service->splitNombre('Pérez');
        $this->assertEquals('Pérez', $soloUno);
    }
}
