<?php

namespace App\Support;

use App\Models\User;

/**
 * Regla de edición uno-a-uno: solo quien creó una cuenta puede editarla,
 * activarla/desactivarla o eliminarla. Súper Administrador es la única
 * excepción — gestiona cualquier cuenta del sistema.
 */
final class UserOwnershipAccess
{
    public static function canManage(User $actor, User $target): bool
    {
        if (TrainingCenterAccess::isSuperAdmin($actor)) {
            return true;
        }

        // BUG-20260813-033 — created_by_user_id se agregó sin backfill: las
        // cuentas creadas antes de esa migración quedan con este campo en
        // null para siempre (no hay forma de reconstruir quién las creó).
        // No aplicamos la regla uno-a-uno cuando el dato simplemente no
        // existe; cada llamador de canManage() ya filtra antes por centro y
        // rol (ver TrainingCenterAccess::scopeUserQueryForList y los
        // ensureXxx de cada controlador), así que esto no abre gestión fuera
        // de ese alcance — solo deja de bloquear cuentas huérfanas dentro de
        // él.
        if ($target->created_by_user_id === null) {
            return true;
        }

        return (int) $target->created_by_user_id === (int) $actor->id;
    }
}
