<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProyectoPolicy
{
    /**
     * El investigador solo puede ver sus propios proyectos.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->project_creator_id === $user->id;
    }

    /**
     * El investigador solo puede editar sus propios proyectos.
     */
    public function update(User $user, Project $project): bool
    {
        return $project->project_creator_id === $user->id;
    }

    /**
     * Solo puede eliminar si es el creador y no tiene productos registrados.
     */
    public function delete(User $user, Project $project): bool
    {
        if ($project->project_creator_id !== $user->id) {
            return false;
        }

        return ! $project->products()->exists();
    }
}
