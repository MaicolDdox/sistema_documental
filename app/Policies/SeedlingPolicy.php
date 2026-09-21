<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Seedling;
use App\Models\User;

/**
 * BUG-20260914-004 — Centraliza la regla de multi-tenancy para Seedling.
 * Reemplaza los checkCentroFormacion() duplicados en los controladores de
 * DirectorSemilleros que usaban campos indirectos (leader/creator) y causaron
 * los bugs 001-003. Aquí se usa el campo autoritativo training_center_id
 * del propio semillero, con fallback al creator solo cuando ese campo es null
 * (registros antiguos sin centro directo).
 *
 * Gate::before() (AppServiceProvider) ya garantiza que super_administrador
 * y administrador_sistema reciben true antes de que estas comprobaciones
 * se ejecuten — no es necesario repetir ese check aquí.
 */
class SeedlingPolicy
{
    public function view(User $user, Seedling $seedling): bool
    {
        return $this->sameCentro($user, $seedling);
    }

    public function create(User $user): bool
    {
        // Cualquier usuario con training_center_id puede crear semilleros
        // en su propio centro; la asignación del centro se hace en el
        // controlador (training_center_id = auth()->user()->training_center_id).
        return $user->training_center_id !== null;
    }

    public function update(User $user, Seedling $seedling): bool
    {
        return $this->sameCentro($user, $seedling);
    }

    public function delete(User $user, Seedling $seedling): bool
    {
        return $this->sameCentro($user, $seedling);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    private function sameCentro(User $user, Seedling $seedling): bool
    {
        if (! $user->training_center_id) {
            return false;
        }

        // Campo autoritativo directo; si es null (registro legacy sin centro
        // directo), se cae al centro del creador como fallback.
        $centerOfRecord = $seedling->training_center_id
            ?? $seedling->creator?->training_center_id;

        return $centerOfRecord !== null
            && (int) $user->training_center_id === (int) $centerOfRecord;
    }
}
