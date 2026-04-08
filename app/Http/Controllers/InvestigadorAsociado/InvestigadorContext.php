<?php

namespace App\Http\Controllers\InvestigadorAsociado;

use App\Models\ResearchGroupUser;
use Illuminate\Support\Facades\Auth;

/**
 * Resuelve y proporciona el ID del grupo de investigación
 * al que pertenece el investigador autenticado.
 *
 * Se usa como trait en todos los controllers del módulo para
 * centralizar la resolución y evitar queries repetidas.
 */
trait InvestigadorContext
{
    private ?int $resolvedGrupoId = null;

    /**
     * Retorna el research_group_id del usuario autenticado
     * como investigador. Lanza 403 si no está vinculado a ningún grupo.
     */
    protected function getGrupoId(): int
    {
        if ($this->resolvedGrupoId !== null) {
            return $this->resolvedGrupoId;
        }

        $pivot = ResearchGroupUser::where('user_id', Auth::id())
            ->whereIn('rol', ['investigador_asociado', 'investigador_lider', 'integrante', 'investigador', 'director'])
            ->first();

        abort_unless($pivot !== null, 403, 'No tienes un grupo de investigación asignado.');

        $this->resolvedGrupoId = $pivot->research_group_id;

        return $this->resolvedGrupoId;
    }

    /**
     * Verifica que el recurso solicitado corresponde al grupo
     * del investigador autenticado. Aborta con 403 si no coincide.
     */
    protected function authorizeGrupo(int $grupoId): void
    {
        abort_unless($this->getGrupoId() === $grupoId, 403, 'No tienes acceso a este recurso.');
    }
}
