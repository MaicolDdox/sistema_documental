<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Support\UserOwnershipAccess;

/**
 * BUG-20260914-004 — Centraliza la regla de multi-tenancy + propiedad para User.
 * Combina dos checks ya existentes en los controladores y que estaban duplicados:
 *   1. Aislamiento por centro: el actor solo gestiona usuarios de su propio training_center_id.
 *   2. Regla uno-a-uno: solo quien creó la cuenta puede gestionarla (UserOwnershipAccess).
 *
 * Gate::before() (AppServiceProvider) ya garantiza que super_administrador
 * y administrador_sistema reciben true antes de que estas comprobaciones
 * se ejecuten, por lo que los métodos aquí solo se evalúan para los demás roles.
 */
class UserPolicy
{
    public function view(User $actor, User $target): bool
    {
        return $this->canManage($actor, $target);
    }

    public function update(User $actor, User $target): bool
    {
        return $this->canManage($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        return $this->canManage($actor, $target);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    private function canManage(User $actor, User $target): bool
    {
        // Aislamiento de centro: cuando ambos tienen centro asignado,
        // deben coincidir. Si alguno no tiene centro el check de propiedad
        // (UserOwnershipAccess) actúa como segundo filtro.
        if ($actor->training_center_id
            && $target->training_center_id
            && (int) $actor->training_center_id !== (int) $target->training_center_id) {
            return false;
        }

        // Regla uno-a-uno: delegar a la clase canónica para evitar duplicación.
        return UserOwnershipAccess::canManage($actor, $target);
    }
}
