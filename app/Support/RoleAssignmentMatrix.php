<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Matriz de creación de usuarios exclusiva del rediseño de roles: un solo
 * rol puede crear/asignar cada rol, sin solapes.
 *
 * super_administrador  -> cualquier rol
 * administrador_sistema -> director_semilleros, co_investigador
 * director_semilleros   -> lider_semillero
 * lider_semillero       -> lider_proyecto
 */
final class RoleAssignmentMatrix
{
    /**
     * @return list<string>
     */
    public static function assignableRolesFor(?User $auth): array
    {
        if (! $auth) {
            return [];
        }
        if (TrainingCenterAccess::isSuperAdmin($auth)) {
            return Role::pluck('name')->all();
        }
        if ($auth->hasRole('administrador_sistema')) {
            return ['director_semilleros', 'co_investigador'];
        }
        if ($auth->hasRole('director_semilleros')) {
            return ['lider_semillero'];
        }
        if ($auth->hasRole('lider_semillero')) {
            return ['lider_proyecto'];
        }

        return [];
    }

    /**
     * FEAT-20260830-001: universo de roles elegibles como "adicionales" al
     * crear un usuario con rol principal $primaryRole — todos excepto
     * super_administrador (nunca se otorga como adicional, solo como
     * principal, y solo super_administrador puede asignarlo) y excepto el
     * propio rol principal que se está creando.
     *
     * @return list<string>
     */
    public static function additionalRoleOptionNamesFor(string $primaryRole): array
    {
        return Role::where('name', '!=', 'super_administrador')
            ->where('name', '!=', $primaryRole)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * FEAT-20260830-001 (edición): sincroniza los roles adicionales de un
     * usuario ya existente — agrega los marcados que le faltan y quita los
     * que estaban asignados dentro del universo de "adicionales posibles"
     * pero ya no están marcados. Nunca toca roles fuera de ese universo
     * (el rol principal y super_administrador nunca se tocan aquí).
     *
     * @param  array<int, string>  $requestedAdditionalRoles  Lo que llegó del formulario, SIN sanear.
     */
    public static function syncAdditionalRoles(User $user, string $primaryRole, array $requestedAdditionalRoles, bool $canManage): void
    {
        if (! $canManage) {
            return;
        }

        $allowed = self::additionalRoleOptionNamesFor($primaryRole);
        $sanitized = array_intersect($requestedAdditionalRoles, $allowed);

        foreach ($allowed as $roleName) {
            $shouldHave = in_array($roleName, $sanitized, true);
            $has = $user->hasRole($roleName);
            if ($shouldHave && ! $has) {
                $user->assignRole($roleName);
            } elseif (! $shouldHave && $has) {
                $user->removeRole($roleName);
            }
        }
    }
}
