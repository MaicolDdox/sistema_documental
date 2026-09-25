<?php

namespace Tests\Feature\Regression;

use App\Models\City;
use App\Models\Department;
use App\Models\GrupoInvestigacion;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\RoleAssignmentMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-20260922-066 — co_investigador_gdi asignado como rol adicional/secundario
 * (vía el checkbox genérico de "roles adicionales") quedaba sin
 * grupo_investigacion_id: el rol se otorgaba, pero nadie preguntaba a qué
 * grupo de investigación pertenecía el usuario. Sus productos Minciencias
 * quedaban huérfanos (grupo_investigacion_id null), invisibles para
 * cualquier director_grupo_investigacion — solo el flujo dedicado
 * (DirectorGrupoInvestigacion\CoInvestigadorController::store()) vinculaba
 * el grupo correctamente.
 *
 * Este test verifica:
 * - RoleAssignmentMatrix::syncAdditionalRoles() asigna grupo_investigacion_id
 *   cuando co_investigador_gdi entra en los roles adicionales.
 * - Lo limpia (null) cuando co_investigador_gdi se desmarca.
 * - Admin\UsuarioController::update() exige grupo_investigacion_id cuando el
 *   checkbox co_investigador_gdi está marcado, y lo valida contra el centro
 *   del usuario editado.
 * - SuperAdmin\AdminUsuarioController::store() exige y guarda
 *   grupo_investigacion_id al crear un administrador_sistema con
 *   co_investigador_gdi como adicional.
 */
class BUG20260922066Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentroConGrupo(string $sufijo): array
    {
        $depto = Department::create(['nombre' => "Depto BUG-066{$sufijo}"]);
        $ciudad = City::create(['nombre' => "Ciudad BUG-066{$sufijo}", 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => "Centro BUG-066{$sufijo}", 'codigo' => "B066{$sufijo}", 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $grupo = GrupoInvestigacion::create([
            'training_center_id' => $centro->id,
            'creator_id' => User::factory()->create(['training_center_id' => $centro->id])->id,
            'nombre' => "Grupo BUG-066{$sufijo}",
            'codigo' => "GBUG066{$sufijo}",
            'estado' => 'activo',
        ]);

        return [$centro, $grupo];
    }

    public function test_sync_additional_roles_asigna_grupo_investigacion_id_al_marcar_co_investigador_gdi(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        [$centro, $grupo] = $this->crearCentroConGrupo('A');

        $usuario = User::factory()->create(['training_center_id' => $centro->id]);
        $usuario->assignRole('lider_semillero');

        RoleAssignmentMatrix::syncAdditionalRoles(
            $usuario,
            'lider_semillero',
            ['co_investigador_gdi'],
            canManage: true,
            grupoInvestigacionId: $grupo->id,
        );

        $usuario->refresh();
        $this->assertTrue($usuario->hasRole('co_investigador_gdi'));
        $this->assertSame($grupo->id, $usuario->grupo_investigacion_id);
    }

    public function test_sync_additional_roles_limpia_grupo_investigacion_id_al_desmarcar_co_investigador_gdi(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        [$centro, $grupo] = $this->crearCentroConGrupo('B');

        $usuario = User::factory()->create(['training_center_id' => $centro->id, 'grupo_investigacion_id' => $grupo->id]);
        $usuario->assignRole('lider_semillero');
        $usuario->assignRole('co_investigador_gdi');

        RoleAssignmentMatrix::syncAdditionalRoles(
            $usuario,
            'lider_semillero',
            [],
            canManage: true,
        );

        $usuario->refresh();
        $this->assertFalse($usuario->hasRole('co_investigador_gdi'));
        $this->assertNull($usuario->grupo_investigacion_id);
    }

    public function test_admin_update_exige_grupo_investigacion_id_si_marca_co_investigador_gdi(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        [$centro] = $this->crearCentroConGrupo('C');

        $adminSistema = User::factory()->create(['training_center_id' => $centro->id]);
        $adminSistema->assignRole('administrador_sistema');

        $objetivo = User::factory()->create(['training_center_id' => $centro->id, 'created_by_user_id' => $adminSistema->id]);
        $objetivo->assignRole('director_semilleros');
        $objetivo->forceFill(['primary_role_name' => 'director_semilleros'])->save();

        $response = $this->actingAs($adminSistema)->put(route('admin.usuarios.update', $objetivo->id), [
            'nombre' => 'Juan', 'apellido' => 'Pérez',
            'numero_documento' => $objetivo->numero_documento,
            'email' => $objetivo->email,
            'rol' => 'director_semilleros',
            'tiene_mas_roles' => '1',
            'additional_roles' => ['co_investigador_gdi'],
        ]);

        $response->assertSessionHasErrors('grupo_investigacion_id');
        $objetivo->refresh();
        $this->assertFalse($objetivo->hasRole('co_investigador_gdi'));
    }

    public function test_admin_update_asigna_co_investigador_gdi_con_grupo_correcto(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        [$centro, $grupo] = $this->crearCentroConGrupo('D');

        $adminSistema = User::factory()->create(['training_center_id' => $centro->id]);
        $adminSistema->assignRole('administrador_sistema');

        $objetivo = User::factory()->create(['training_center_id' => $centro->id, 'created_by_user_id' => $adminSistema->id]);
        $objetivo->assignRole('director_semilleros');
        $objetivo->forceFill(['primary_role_name' => 'director_semilleros'])->save();

        $response = $this->actingAs($adminSistema)->put(route('admin.usuarios.update', $objetivo->id), [
            'nombre' => 'Juan', 'apellido' => 'Pérez',
            'numero_documento' => $objetivo->numero_documento,
            'email' => $objetivo->email,
            'rol' => 'director_semilleros',
            'tiene_mas_roles' => '1',
            'additional_roles' => ['co_investigador_gdi'],
            'grupo_investigacion_id' => $grupo->id,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $objetivo->refresh();
        $this->assertTrue($objetivo->hasRole('co_investigador_gdi'));
        $this->assertSame($grupo->id, $objetivo->grupo_investigacion_id);
    }

    public function test_admin_update_rechaza_grupo_de_otro_centro(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        [$centro] = $this->crearCentroConGrupo('E');
        [, $grupoAjeno] = $this->crearCentroConGrupo('F');

        $adminSistema = User::factory()->create(['training_center_id' => $centro->id]);
        $adminSistema->assignRole('administrador_sistema');

        $objetivo = User::factory()->create(['training_center_id' => $centro->id, 'created_by_user_id' => $adminSistema->id]);
        $objetivo->assignRole('director_semilleros');
        $objetivo->forceFill(['primary_role_name' => 'director_semilleros'])->save();

        $response = $this->actingAs($adminSistema)->put(route('admin.usuarios.update', $objetivo->id), [
            'nombre' => 'Juan', 'apellido' => 'Pérez',
            'numero_documento' => $objetivo->numero_documento,
            'email' => $objetivo->email,
            'rol' => 'director_semilleros',
            'tiene_mas_roles' => '1',
            'additional_roles' => ['co_investigador_gdi'],
            'grupo_investigacion_id' => $grupoAjeno->id,
        ]);

        $response->assertSessionHasErrors('grupo_investigacion_id');
        $objetivo->refresh();
        $this->assertFalse($objetivo->hasRole('co_investigador_gdi'));
    }

    public function test_super_admin_crea_administrador_sistema_con_co_investigador_gdi_y_grupo(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        [$centro, $grupo] = $this->crearCentroConGrupo('G');

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_administrador');

        $response = $this->actingAs($superAdmin)->post(route('super-admin.administradores.store'), [
            'nombre' => 'Ana', 'apellido' => 'Gómez',
            'tipo_documento' => 'cedula ciudadana',
            'numero_documento' => '999888777',
            'email' => 'ana.gomez@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'training_center_id' => $centro->id,
            'tiene_mas_roles' => '1',
            'additional_roles' => ['co_investigador_gdi'],
            'grupo_investigacion_id' => $grupo->id,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $usuario = User::where('email', 'ana.gomez@example.com')->firstOrFail();
        $this->assertTrue($usuario->hasRole('co_investigador_gdi'));
        $this->assertSame($grupo->id, $usuario->grupo_investigacion_id);
    }
}
