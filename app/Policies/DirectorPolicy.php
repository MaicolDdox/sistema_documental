<?php

namespace App\Policies;

use App\Enums\RolGrupoEnum;
use App\Models\ResearchGroup;
use App\Models\ResearchGroupUser;
use App\Models\User;

class DirectorPolicy
{
    /**
     * El usuario debe tener el rol Spatie 'director_investigacion'.
     * Si no lo tiene, deniega todo sin entrar al método específico.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->hasRole('director_investigacion')) {
            return false;
        }

        return null; // continúa al método específico
    }

    /**
     * Valida que el director es efectivamente el director del grupo dado.
     */
    public function esDirectorDelGrupo(User $user, ResearchGroup $grupo): bool
    {
        return ResearchGroupUser::where('user_id', $user->id)
            ->where('research_group_id', $grupo->id)
            ->where('rol', RolGrupoEnum::Director)
            ->exists();
    }

    /**
     * El director no puede operar sobre su propio usuario como investigador.
     */
    public function operar(User $director, User $objetivo): bool
    {
        return $director->id !== $objetivo->id;
    }
}
