<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Services\Admin\RoleAssignmentService;
use App\Support\RoleAssignmentMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * BUG-20260914-006 — Consolidar asignación de roles dispersa en un único Service.
 *
 * Antes de este fix, UserCreationService::crearUsuario() y
 * RoleAssignmentMatrix::syncAdditionalRoles() asignaban roles sin llamar a
 * validateCentroBoundRoleAssignment(), lo que permitía asignar roles
 * CENTRO_BOUND_ROLE_NAMES sin training_center_id.
 *
 * Este test verifica:
 * - RoleAssignmentService::assign() lanza ValidationException para roles
 *   CENTRO_BOUND cuando el usuario carece de training_center_id.
 * - RoleAssignmentService::assign() tiene éxito cuando el usuario tiene TC.
 * - RoleAssignmentService::assign() tiene éxito para roles no-CENTRO_BOUND
 *   independientemente del TC.
 * - RoleAssignmentService::assignIfMissing() no reasigna si ya tiene el rol.
 * - UserCreationService::crearUsuario() ahora lanza ValidationException
 *   al intentar asignar un rol CENTRO_BOUND a un usuario sin TC (gap real).
 * - RoleAssignmentMatrix::syncAdditionalRoles() ahora lanza ValidationException
 *   al intentar asignar un rol adicional CENTRO_BOUND sin TC (gap real).
 */
class BUG20260914006Test extends TestCase
{
    use RefreshDatabase;

    private TrainingCenter $centro;

    private RoleAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto Test']);
        $ciudad = City::create(['nombre' => 'Ciudad Test', 'department_id' => $depto->id]);
        $this->centro = TrainingCenter::create([
            'nombre' => 'Centro Test', 'codigo' => 'CT1', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $this->service = app(RoleAssignmentService::class);
    }

    // ─────────────────────────────────────────────
    // RoleAssignmentService::assign()
    // ─────────────────────────────────────────────

    public function test_assign_lanza_exception_para_rol_centro_bound_sin_training_center(): void
    {
        $usuario = User::factory()->create(['training_center_id' => null, 'estado' => EstadoEnum::Activo]);

        $this->expectException(ValidationException::class);
        $this->service->assign($usuario, 'director_semilleros');
    }

    public function test_assign_asigna_rol_centro_bound_con_training_center(): void
    {
        $usuario = User::factory()->create([
            'training_center_id' => $this->centro->id,
            'estado' => EstadoEnum::Activo,
        ]);

        $this->service->assign($usuario, 'director_semilleros');

        $this->assertTrue($usuario->hasRole('director_semilleros'));
    }

    public function test_assign_asigna_rol_no_centro_bound_sin_training_center(): void
    {
        // super_administrador no está en CENTRO_BOUND_ROLE_NAMES
        $usuario = User::factory()->create(['training_center_id' => null, 'estado' => EstadoEnum::Activo]);

        $this->service->assign($usuario, 'super_administrador');

        $this->assertTrue($usuario->hasRole('super_administrador'));
    }

    public function test_assign_ignora_rol_vacio(): void
    {
        $usuario = User::factory()->create(['training_center_id' => null, 'estado' => EstadoEnum::Activo]);

        // No debe lanzar excepción con string vacío
        $this->service->assign($usuario, '');

        $this->assertCount(0, $usuario->roles);
    }

    // ─────────────────────────────────────────────
    // RoleAssignmentService::assignIfMissing()
    // ─────────────────────────────────────────────

    public function test_assign_if_missing_no_reasigna_rol_ya_existente(): void
    {
        $usuario = User::factory()->create([
            'training_center_id' => $this->centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $usuario->assignRole('director_semilleros');

        // No debe lanzar ni duplicar
        $this->service->assignIfMissing($usuario, 'director_semilleros');

        $this->assertCount(1, $usuario->roles()->where('name', 'director_semilleros')->get());
    }

    public function test_assign_if_missing_lanza_exception_para_rol_centro_bound_sin_tc(): void
    {
        $usuario = User::factory()->create(['training_center_id' => null, 'estado' => EstadoEnum::Activo]);

        $this->expectException(ValidationException::class);
        $this->service->assignIfMissing($usuario, 'lider_semillero');
    }

    // ─────────────────────────────────────────────
    // Gap real #1 — UserCreationService ahora valida
    // ─────────────────────────────────────────────

    public function test_user_creation_service_lanza_exception_rol_centro_bound_con_tc_null(): void
    {
        // La validación previa ($algunRolExigeCentro && $trainingCenterId === null)
        // ya cubría el caso de trainingCenterId null, pero ahora también pasa
        // por validateCentroBoundRoleAssignment dentro de la transacción.
        $this->expectException(ValidationException::class);

        app(\App\Services\Admin\UserCreationService::class)->crearUsuario([
            'email' => 'test@example.com',
            'numero_documento' => '123456789',
            'tipo_documento' => 'cedula_ciudadania',
            'password' => 'password',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'rol' => 'director_semilleros',
        ], null);
    }

    // ─────────────────────────────────────────────
    // Gap real #2 — syncAdditionalRoles ahora valida
    // ─────────────────────────────────────────────

    public function test_sync_additional_roles_lanza_exception_para_adicional_centro_bound_sin_tc(): void
    {
        // Usuario sin centro: antes syncAdditionalRoles asignaba igual, ahora
        // lanza ValidationException gracias a RoleAssignmentService::assign().
        $usuario = User::factory()->create([
            'training_center_id' => null,
            'estado' => EstadoEnum::Activo,
        ]);
        $usuario->assignRole('super_administrador');

        $this->expectException(ValidationException::class);

        // lider_semillero es CENTRO_BOUND — sin TC debe fallar
        RoleAssignmentMatrix::syncAdditionalRoles(
            $usuario,
            'super_administrador',
            ['lider_semillero'],
            canManage: true
        );
    }

    public function test_sync_additional_roles_asigna_adicional_centro_bound_con_tc(): void
    {
        $usuario = User::factory()->create([
            'training_center_id' => $this->centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $usuario->assignRole('director_semilleros');

        // lider_semillero es CENTRO_BOUND pero el usuario tiene TC — debe funcionar
        RoleAssignmentMatrix::syncAdditionalRoles(
            $usuario,
            'director_semilleros',
            ['lider_semillero'],
            canManage: true
        );

        $usuario->refresh();
        $this->assertTrue($usuario->hasRole('lider_semillero'));
    }

    public function test_sync_additional_roles_sin_permiso_no_modifica_roles(): void
    {
        $usuario = User::factory()->create([
            'training_center_id' => $this->centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $usuario->assignRole('director_semilleros');

        RoleAssignmentMatrix::syncAdditionalRoles(
            $usuario,
            'director_semilleros',
            ['lider_semillero'],
            canManage: false
        );

        $this->assertFalse($usuario->hasRole('lider_semillero'));
    }
}
