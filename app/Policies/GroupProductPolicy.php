<?php

namespace App\Policies;

use App\Enums\RolGrupoEnum;
use App\Models\GroupProduct;
use App\Models\ResearchGroupUser;
use App\Models\User;

class GroupProductPolicy
{
    /**
     * El director solo puede revisar un GroupProduct si su author_id
     * pertenece al mismo grupo del director.
     */
    public function revisar(User $director, GroupProduct $producto): bool
    {
        if (! $director->hasRole('director_investigacion')) {
            return false;
        }

        $grupoIdDirector = ResearchGroupUser::where('user_id', $director->id)
            ->where('rol', RolGrupoEnum::Director)
            ->value('research_group_id');

        if ($grupoIdDirector === null) {
            return false;
        }

        // Verificar que el autor del producto pertenece al mismo grupo
        return ResearchGroupUser::where('research_group_id', $grupoIdDirector)
            ->where('user_id', $producto->author_id)
            ->exists();
    }
}
