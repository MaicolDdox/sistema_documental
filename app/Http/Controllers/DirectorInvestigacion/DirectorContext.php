<?php

namespace App\Http\Controllers\DirectorInvestigacion;

use App\Enums\RolGrupoEnum;
use App\Models\ResearchGroupUser;
use Illuminate\Support\Facades\Auth;

/**
 * Resuelve y proporciona el ID del grupo de investigación
 * al que pertenece el director autenticado.
 *
 * Se usa como trait en todos los controllers del módulo para
 * centralizar la resolución y evitar queries repetidas.
 */
trait DirectorContext
{
    private ?int $resolvedGrupoId = null;

    /**
     * Retorna el research_group_id del usuario autenticado
     * como director. Lanza 403 si no está vinculado.
     */
    protected function getGrupoId(): int
    {
        if ($this->resolvedGrupoId !== null) {
            return $this->resolvedGrupoId;
        }

        $pivot = ResearchGroupUser::where('user_id', Auth::id())
            ->where('rol', RolGrupoEnum::Director)
            ->first();

        abort_unless($pivot !== null, 403, 'No tienes un grupo de investigación asignado.');

        $this->resolvedGrupoId = $pivot->research_group_id;

        return $this->resolvedGrupoId;
    }

    /**
     * Verifica que el recurso solicitado (por su grupo_id) corresponde
     * al grupo del director. Aborta con 403 si no coincide.
     */
    protected function authorizeGrupo(int $grupoId): void
    {
        abort_unless($this->getGrupoId() === $grupoId, 403, 'No tienes acceso a este recurso.');
    }
}
