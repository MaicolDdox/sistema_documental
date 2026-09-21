<?php

namespace App\Support;

use App\Models\User;
use App\Services\Admin\RoleAssignmentService;

/**
 * Matriz de creación de usuarios exclusiva de la reforma de roles GDI/SDI:
 * un solo rol puede crear/asignar cada rol, sin solapes.
 *
 * super_administrador    -> cualquier rol
 * administrador_sistema  -> director_semilleros, director_grupo_investigacion
 * director_semilleros    -> lider_semillero, co_investigador_sdi
 * lider_semillero        -> lider_proyecto
 * director_grupo_investigacion -> co_investigador_gdi
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
            return RoleModuleLinks::LOGIN_ROLE_PRIORITY;
        }
        if ($auth->hasRole('administrador_sistema')) {
            return ['director_semilleros', 'director_grupo_investigacion'];
        }
        if ($auth->hasRole('director_semilleros')) {
            return ['lider_semillero', 'co_investigador_sdi'];
        }
        if ($auth->hasRole('lider_semillero')) {
            return ['lider_proyecto'];
        }
        if ($auth->hasRole('director_grupo_investigacion')) {
            return ['co_investigador_gdi'];
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
     * administrador_sistema también se excluye salvo que quien está viendo
     * el formulario ($auth) sea super_administrador: solo el súper admin
     * puede ver/otorgar administrador_sistema como rol adicional.
     *
     * @return list<string>
     */
    public static function additionalRoleOptionNamesFor(string $primaryRole, ?User $auth = null): array
    {
        $excluded = ['super_administrador', $primaryRole];
        if (! TrainingCenterAccess::isSuperAdmin($auth)) {
            $excluded[] = 'administrador_sistema';
        }

        $names = array_diff(RoleModuleLinks::LOGIN_ROLE_PRIORITY, $excluded);
        sort($names);

        return array_values($names);
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
    public static function syncAdditionalRoles(User $user, string $primaryRole, array $requestedAdditionalRoles, bool $canManage, ?User $auth = null): void
    {
        if (! $canManage) {
            return;
        }

        $allowed = self::additionalRoleOptionNamesFor($primaryRole, $auth);
        $sanitized = array_intersect($requestedAdditionalRoles, $allowed);

        foreach ($allowed as $roleName) {
            $shouldHave = in_array($roleName, $sanitized, true);
            $has = $user->hasRole($roleName);
            if ($shouldHave && ! $has) {
                // BUG-20260914-006: rutar por RoleAssignmentService valida
                // restricción de centro también para roles adicionales.
                app(RoleAssignmentService::class)->assign($user, $roleName);
            } elseif (! $shouldHave && $has) {
                $user->removeRole($roleName);
            }
        }
    }
}
