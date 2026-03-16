<?php

namespace App\Services\Director;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Enums\RolGrupoEnum;
use App\Models\GroupProduct;
use App\Models\ResearchGroup;
use App\Models\ResearchGroupUser;
use App\Models\User;
use Illuminate\Support\Collection;

class ReporteService
{
    /**
     * Productos agrupados por investigador, filtrados por grupo.
     */
    public function productosPorInvestigador(int $grupoId, array $filtros = []): Collection
    {
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)
            ->pluck('user_id');

        return GroupProduct::with(['author.person', 'product', 'mincienciasTypology'])
            ->whereIn('author_id', $userIds)
            ->when(isset($filtros['estado_revision']), fn($q) =>
                $q->where('estado_revision', $filtros['estado_revision'])
            )
            ->when(isset($filtros['anio']), fn($q) =>
                $q->where('anio_publicacion', $filtros['anio'])
            )
            ->get()
            ->groupBy('author_id')
            ->map(fn($productos, $authorId) => [
                'investigador' => $productos->first()->author?->person?->nombre_completo
                    ?? $productos->first()->author?->email
                    ?? 'Desconocido',
                'total'        => $productos->count(),
                'productos'    => $productos,
            ])
            ->values();
    }

    /**
     * Productos agrupados por año de publicación, filtrados por grupo.
     */
    public function productosPorAnio(int $grupoId, array $filtros = []): Collection
    {
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)
            ->pluck('user_id');

        return GroupProduct::whereIn('author_id', $userIds)
            ->when(isset($filtros['estado_revision']), fn($q) =>
                $q->where('estado_revision', $filtros['estado_revision'])
            )
            ->selectRaw('anio_publicacion, count(*) as total')
            ->groupBy('anio_publicacion')
            ->orderBy('anio_publicacion', 'desc')
            ->get();
    }

    /**
     * Resumen aprobados vs rechazados del grupo.
     */
    public function aprobadosVsRechazados(int $grupoId, array $filtros = []): array
    {
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)
            ->pluck('user_id');

        $base = GroupProduct::whereIn('author_id', $userIds)
            ->when(isset($filtros['anio']), fn($q) =>
                $q->where('anio_publicacion', $filtros['anio'])
            );

        return [
            'pendiente'   => (clone $base)->where('estado_revision', EstadoRevisionEnum::Pendiente)->count(),
            'en_revision' => (clone $base)->where('estado_revision', EstadoRevisionEnum::EnRevision)->count(),
            'aprobado'    => (clone $base)->where('estado_revision', EstadoRevisionEnum::Aprobado)->count(),
            'rechazado'   => (clone $base)->where('estado_revision', EstadoRevisionEnum::Rechazado)->count(),
            'total'       => (clone $base)->count(),
        ];
    }

    /**
     * Actividad general del grupo: investigadores activos + totales.
     */
    public function actividadGeneral(int $grupoId): array
    {
        $grupo = ResearchGroup::withCount(['researchGroupUsers', 'projects'])->findOrFail($grupoId);
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');

        return [
            'grupo'                  => $grupo,
            'total_investigadores'   => $userIds->count(),
            'total_productos'        => GroupProduct::whereIn('author_id', $userIds)->count(),
            'productos_aprobados'    => GroupProduct::whereIn('author_id', $userIds)
                ->where('estado_revision', EstadoRevisionEnum::Aprobado)->count(),
        ];
    }
}
