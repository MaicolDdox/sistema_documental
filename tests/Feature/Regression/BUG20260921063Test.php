<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use App\Support\RoleAssignmentMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-20260921-063 — Checkbox de "roles adicionales" ofrecía administrador_sistema
 * a cualquier usuario que pudiera llegar al formulario.
 *
 * RoleAssignmentMatrix::additionalRoleOptionNamesFor() solo excluía
 * super_administrador (nunca asignable como adicional) y el propio rol
 * principal que se estaba creando — sin importar quién veía el formulario.
 * Eso permitía que, por ejemplo, un administrador_sistema creando un
 * director_semilleros viera "administrador_sistema" como checkbox de rol
 * adicional, pudiendo otorgarse (o a otros) permisos de administrador.
 *
 * Este test verifica:
 * - Sin $auth (o con un $auth que no es super_administrador): el universo de
 *   roles adicionales excluye tanto super_administrador como
 *   administrador_sistema.
 * - Con $auth = super_administrador: el universo incluye administrador_sistema,
 *   pero sigue excluyendo super_administrador (nunca se otorga como adicional).
 * - syncAdditionalRoles() sanea (ignora) 'administrador_sistema' del request
 *   cuando quien sincroniza no es super_administrador, incluso si el
 *   formulario lo envía.
 */
class BUG20260921063Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_sin_auth_excluye_administrador_sistema_y_super_administrador(): void
    {
        $roles = RoleAssignmentMatrix::additionalRoleOptionNamesFor('director_semilleros');

        $this->assertNotContains('administrador_sistema', $roles);
        $this->assertNotContains('super_administrador', $roles);
        $this->assertNotContains('director_semilleros', $roles);
    }

    public function test_auth_administrador_sistema_excluye_administrador_sistema_y_super_administrador(): void
    {
        $adminSistema = User::factory()->create();
        $adminSistema->assignRole('administrador_sistema');

        $roles = RoleAssignmentMatrix::additionalRoleOptionNamesFor('director_semilleros', $adminSistema);

        $this->assertNotContains('administrador_sistema', $roles);
        $this->assertNotContains('super_administrador', $roles);
    }

    public function test_auth_super_administrador_incluye_administrador_sistema_pero_no_super_administrador(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_administrador');

        $roles = RoleAssignmentMatrix::additionalRoleOptionNamesFor('director_semilleros', $superAdmin);

        $this->assertContains('administrador_sistema', $roles);
        $this->assertNotContains('super_administrador', $roles);
    }

    public function test_sync_additional_roles_sanea_administrador_sistema_si_quien_sincroniza_no_es_super_admin(): void
    {
        $adminSistema = User::factory()->create();
        $adminSistema->assignRole('administrador_sistema');

        $usuario = User::factory()->create();
        $usuario->assignRole('director_semilleros');

        RoleAssignmentMatrix::syncAdditionalRoles(
            $usuario,
            'director_semilleros',
            ['administrador_sistema'],
            canManage: true,
            auth: $adminSistema,
        );

        $usuario->refresh();
        $this->assertFalse($usuario->hasRole('administrador_sistema'));
    }
}
