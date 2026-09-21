<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\User;
use App\Support\TrainingCenterAccess;
use Illuminate\Validation\ValidationException;

/**
 * Punto único de asignación de roles con validación de centro de formación.
 *
 * BUG-20260914-006: antes de este service, las 5 llamadas a assignRole()
 * dispersas en el codebase tenían validaciones inconsistentes.
 * UserCreationService y RoleAssignmentMatrix::syncAdditionalRoles() no
 * llamaban a validateCentroBoundRoleAssignment(), lo que permitía asignar
 * roles CENTRO_BOUND_ROLE_NAMES sin training_center_id asignado al usuario.
 *
 * Todo assignRole() del dominio de usuarios pasa ahora por aquí.
 */
final class RoleAssignmentService
{
    // ─────────────────────────────────────────────
    // Métodos públicos
    // ─────────────────────────────────────────────

    /**
     * Valida la restricción de centro y asigna el rol al usuario.
     * Idempotente: Spatie no duplica si el usuario ya tiene el rol.
     *
     * @param  string  $messageKey  Clave del campo de error en ValidationException.
     *
     * @throws ValidationException si el rol exige training_center_id y el usuario no lo tiene,
     *                             o si el actor intenta cruzar a otro centro.
     */
    public function assign(
        User $user,
        string $roleName,
        ?User $actor = null,
        string $messageKey = 'rol'
    ): void {
        if ($roleName === '') {
            return;
        }

        TrainingCenterAccess::validateCentroBoundRoleAssignment($user, $roleName, $actor, $messageKey);
        $user->assignRole($roleName);
    }

    /**
     * Igual que assign() pero solo ejecuta la asignación si el usuario NO
     * tiene aún el rol. Patrón de edición: conserva otros roles existentes
     * sin syncRoles (no quita nada).
     *
     * @param  string  $messageKey  Clave del campo de error en ValidationException.
     *
     * @throws ValidationException si el rol exige training_center_id y el usuario no lo tiene.
     */
    public function assignIfMissing(
        User $user,
        string $roleName,
        ?User $actor = null,
        string $messageKey = 'rol'
    ): void {
        if ($roleName === '') {
            return;
        }

        TrainingCenterAccess::validateCentroBoundRoleAssignment($user, $roleName, $actor, $messageKey);

        if (! $user->hasRole($roleName)) {
            $user->assignRole($roleName);
        }
    }
}
