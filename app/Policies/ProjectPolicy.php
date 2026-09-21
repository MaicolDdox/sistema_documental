<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * BUG-20260914-004 — Centraliza la regla de multi-tenancy para Project.
 * Project no tiene training_center_id directo; pertenece a un Seedling que sí lo tiene.
 * La comprobación navega esa relación para resolver el centro del registro.
 *
 * Gate::before() (AppServiceProvider) ya garantiza que super_administrador
 * y administrador_sistema reciben true antes de que estas comprobaciones
 * se ejecuten.
 */
class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $this->sameCentro($user, $project);
    }

    public function create(User $user): bool
    {
        return $user->training_center_id !== null;
    }

    public function update(User $user, Project $project): bool
    {
        return $this->sameCentro($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->sameCentro($user, $project);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    private function sameCentro(User $user, Project $project): bool
    {
        if (! $user->training_center_id) {
            return false;
        }

        // Navegar la relación seedling para obtener el training_center_id
        // del semillero al que pertenece el proyecto.
        $centerOfRecord = $project->seedling?->training_center_id;

        return $centerOfRecord !== null
            && (int) $user->training_center_id === (int) $centerOfRecord;
    }
}
