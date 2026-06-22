<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\RolGrupoEnum;
use App\Models\ResearchGroup;
use App\Models\ResearchGroupUser;
use App\Models\User;

class ResearchGroupService
{
    /**
     * Si el usuario adquiere un rol investigativo, lo vincula automáticamente
     * al grupo de investigación de su centro de formación.
     * No hace nada si el rol no es investigativo o el usuario no tiene centro.
     */
    public function autoVincularUsuario(User $usuario, string $rol): void
    {
        if (! in_array($rol, ['director_investigacion', 'investigador_asociado'], true)) {
            return;
        }

        if (! $usuario->training_center_id) {
            return;
        }

        $researchGroup = ResearchGroup::where('training_center_id', $usuario->training_center_id)->first();
        if (! $researchGroup) {
            return;
        }

        $rolGrupo = $rol === 'director_investigacion'
            ? RolGrupoEnum::Director
            : RolGrupoEnum::InvestigadorAsociado;

        ResearchGroupUser::firstOrCreate(
            [
                'research_group_id' => $researchGroup->id,
                'user_id'           => $usuario->id,
            ],
            ['rol' => $rolGrupo]
        );
    }
}
