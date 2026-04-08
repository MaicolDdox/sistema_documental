<?php

namespace App\Policies;

use App\Models\ResearchGroup;
use App\Models\ResearchGroupUser;
use App\Models\User;

class GrupoPolicy
{
    /**
     * El investigador solo puede registrar productos en grupos
     * a los que pertenece (validado contra research_group_users).
     */
    public function registrarProducto(User $user, ResearchGroup $researchGroup): bool
    {
        return ResearchGroupUser::where('user_id', $user->id)
            ->where('research_group_id', $researchGroup->id)
            ->whereIn('rol', ['investigador_asociado', 'investigador_lider', 'integrante', 'investigador'])
            ->exists();
    }
}
