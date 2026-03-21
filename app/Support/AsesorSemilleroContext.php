<?php

namespace App\Support;

use App\Models\Seedling;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Semilleros vinculados al asesor (pivot seedling_advisors → external_advisors.user_id)
 * y semillero "activo" en sesión para listados que aplican a un solo semillero a la vez.
 */
class AsesorSemilleroContext
{
    public const SESSION_KEY = 'asesor_active_seedling_id';

    public static function semillerosDelUsuarioAutenticado(): Collection
    {
        return Seedling::query()
            ->whereHas('advisors', function ($q) {
                $q->where('external_advisors.user_id', Auth::id())
                    ->where('seedling_advisors.activo', true);
            })
            ->orderBy('nombre')
            ->get();
    }

    /** Misma consulta con relaciones para reportes PDF. */
    public static function semillerosDelUsuarioAutenticadoConDetalle(): Collection
    {
        return Seedling::query()
            ->with(['researchGroup', 'leader.person', 'members.person'])
            ->whereHas('advisors', function ($q) {
                $q->where('external_advisors.user_id', Auth::id())
                    ->where('seedling_advisors.activo', true);
            })
            ->orderBy('nombre')
            ->get();
    }

    public static function idsSemillerosDelAsesor(): Collection
    {
        return self::semillerosDelUsuarioAutenticado()->pluck('id');
    }

    /**
     * @param  Collection<int, Seedling>|null  $semillerosCache  Evita segunda consulta si ya cargaste la lista.
     */
    public static function semilleroActivo(?Collection $semillerosCache = null): ?Seedling
    {
        $todos = $semillerosCache ?? self::semillerosDelUsuarioAutenticado();
        if ($todos->isEmpty()) {
            return null;
        }

        $sid = session(self::SESSION_KEY);
        if ($sid !== null && $sid !== '' && $todos->pluck('id')->contains((int) $sid)) {
            return $todos->firstWhere('id', (int) $sid);
        }

        $first = $todos->first();
        session([self::SESSION_KEY => $first->id]);

        return $first;
    }

    public static function setSemilleroActivo(int $seedlingId): void
    {
        $todos = self::semillerosDelUsuarioAutenticado();
        if (! $todos->pluck('id')->contains($seedlingId)) {
            abort(403, 'No tienes acceso a este semillero.');
        }
        session([self::SESSION_KEY => $seedlingId]);
    }

    /** Primer semillero del asesor que enlaza el proyecto (autorización por cualquier semillero asignado). */
    public static function semilleroVinculadoAlProyectoParaAsesor(int $projectId): ?Seedling
    {
        $ids = self::idsSemillerosDelAsesor();
        if ($ids->isEmpty()) {
            return null;
        }

        $row = DB::table('project_seedlings')
            ->where('project_id', $projectId)
            ->whereIn('seedling_id', $ids)
            ->first();

        return $row ? Seedling::query()->find($row->seedling_id) : null;
    }
}
