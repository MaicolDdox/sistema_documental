<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GrupoInvestigacion;
use App\Models\User;

/**
 * BUG-20260914-004 — Centraliza la regla de multi-tenancy para GrupoInvestigacion.
 * GrupoInvestigacion tiene un training_center_id directo; la comprobación es
 * una igualdad directa entre el centro del actor y el del recurso.
 *
 * Gate::before() (AppServiceProvider) ya garantiza que super_administrador
 * y administrador_sistema reciben true antes de que estas comprobaciones
 * se ejecuten.
 */
class GrupoInvestigacionPolicy
{
    public function view(User $user, GrupoInvestigacion $grupo): bool
    {
        return $this->sameCentro($user, $grupo);
    }

    public function create(User $user): bool
    {
        return $user->training_center_id !== null;
    }

    public function update(User $user, GrupoInvestigacion $grupo): bool
    {
        return $this->sameCentro($user, $grupo);
    }

    public function delete(User $user, GrupoInvestigacion $grupo): bool
    {
        return $this->sameCentro($user, $grupo);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    private function sameCentro(User $user, GrupoInvestigacion $grupo): bool
    {
        if (! $user->training_center_id) {
            return false;
        }

        return (int) $user->training_center_id === (int) $grupo->training_center_id;
    }
}
