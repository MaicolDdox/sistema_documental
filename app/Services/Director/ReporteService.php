<?php

namespace App\Services\Director;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Enums\RolGrupoEnum;
use App\Models\GroupProduct;
use App\Models\MacroProject;
use App\Models\Project;
use App\Models\ResearchGroup;
use App\Models\ResearchGroupUser;
use App\Models\User;
use Illuminate\Support\Collection;

class ReporteService
{
    // ──────────────────────────────────────────────────────────────────────
    // PRODUCTOS
    // ──────────────────────────────────────────────────────────────────────

    /** Productos agrupados por investigador. */
    public function productosPorInvestigador(int $grupoId, array $filtros = []): Collection
    {
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');

        return GroupProduct::with(['author.person', 'product', 'mincienciasTypology'])
            ->whereIn('author_id', $userIds)
            ->when(isset($filtros['estado_revision']) && $filtros['estado_revision'] !== '', fn($q) =>
                $q->where('estado_revision', $filtros['estado_revision'])
            )
            ->when(isset($filtros['anio']) && $filtros['anio'] !== '', fn($q) =>
                $q->where('anio_publicacion', $filtros['anio'])
            )
            ->when(isset($filtros['investigador_id']) && $filtros['investigador_id'] !== '', fn($q) =>
                $q->where('author_id', $filtros['investigador_id'])
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

    /** Productos agrupados por año de publicación. */
    public function productosPorAnio(int $grupoId, array $filtros = []): Collection
    {
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');

        return GroupProduct::whereIn('author_id', $userIds)
            ->when(isset($filtros['estado_revision']) && $filtros['estado_revision'] !== '', fn($q) =>
                $q->where('estado_revision', $filtros['estado_revision'])
            )
            ->selectRaw('anio_publicacion, count(*) as total')
            ->groupBy('anio_publicacion')
            ->orderBy('anio_publicacion', 'desc')
            ->get();
    }

    /** Distribución de estados de productos. */
    public function aprobadosVsRechazados(int $grupoId, array $filtros = []): array
    {
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id');

        $base = GroupProduct::whereIn('author_id', $userIds)
            ->when(isset($filtros['anio']) && $filtros['anio'] !== '', fn($q) =>
                $q->where('anio_publicacion', $filtros['anio'])
            )
            ->when(isset($filtros['investigador_id']) && $filtros['investigador_id'] !== '', fn($q) =>
                $q->where('author_id', $filtros['investigador_id'])
            );

        return [
            'pendiente'   => (clone $base)->where('estado_revision', EstadoRevisionEnum::Pendiente)->count(),
            'en_revision' => (clone $base)->where('estado_revision', EstadoRevisionEnum::EnRevision)->count(),
            'aprobado'    => (clone $base)->where('estado_revision', EstadoRevisionEnum::Aprobado)->count(),
            'rechazado'   => (clone $base)->where('estado_revision', EstadoRevisionEnum::Rechazado)->count(),
            'total'       => (clone $base)->count(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // PROYECTOS
    // ──────────────────────────────────────────────────────────────────────

    /** Proyectos vinculados al grupo del director. */
    public function resumenProyectos(int $grupoId, $desde = null, $hasta = null): array
    {
        $proyectos = Project::whereHas('researchGroups', fn($q) => $q->where('research_groups.id', $grupoId))
            ->when($desde, fn($q) => $q->whereBetween('projects.created_at', [$desde, $hasta]))
            ->with(['projectCreator.person', 'macroProject', 'researchLine'])
            ->get();

        return [
            'total'     => $proyectos->count(),
            'activos'   => $proyectos->where('estado.value', 'activo')->count(),
            'inactivos' => $proyectos->where('estado.value', 'inactivo')->count(),
            'lista'     => $proyectos,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // MACROPROYECTOS
    // ──────────────────────────────────────────────────────────────────────

    /** Macroproyectos vinculados al grupo (a través de proyectos). */
    public function resumenMacroproyectos(int $grupoId, $desde = null, $hasta = null): array
    {
        $macros = MacroProject::where('research_group_id', $grupoId)
            ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
            ->withCount('projects')
            ->get();

        return [
            'total' => $macros->count(),
            'lista' => $macros,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // INVESTIGADORES
    // ──────────────────────────────────────────────────────────────────────

    /** Investigadores del grupo con métricas individuales. */
    public function investigadoresDetallados(int $grupoId, $desde = null, $hasta = null): Collection
    {
        $pivots = ResearchGroupUser::with('user.person')
            ->where('research_group_id', $grupoId)
            ->where('rol', '!=', RolGrupoEnum::Director)
            ->get();

        return $pivots->map(function ($pivot) use ($desde, $hasta) {
            $user = $pivot->user;
            $userId = $user?->id;

            $productos = GroupProduct::where('author_id', $userId)
                ->when($desde, fn($q) => $q->whereBetween('created_at', [$desde, $hasta]))
                ->get();

            return [
                'id'          => $userId,
                'nombre'      => $user?->person?->nombre_completo ?? $user?->email ?? 'Desconocido',
                'email'       => $user?->email ?? '—',
                'estado'      => $user?->estado?->value ?? 'inactivo',
                'rol'         => $pivot->rol?->value ?? '',
                'cvlac'       => $user?->person?->cvlac_link ?? null,
                'total'       => $productos->count(),
                'aprobados'   => $productos->where('estado_revision.value', 'aprobado')->count(),
                'en_revision' => $productos->where('estado_revision.value', 'en_revision')->count(),
                'pendientes'  => $productos->where('estado_revision.value', 'pendiente')->count(),
                'rechazados'  => $productos->where('estado_revision.value', 'rechazado')->count(),
            ];
        })->values();
    }

    // ──────────────────────────────────────────────────────────────────────
    // ACTIVIDAD GENERAL
    // ──────────────────────────────────────────────────────────────────────

    /** KPIs globales del grupo. */
    public function actividadGeneral(int $grupoId): array
    {
        $grupo   = ResearchGroup::with('trainingCenter')->findOrFail($grupoId);
        $userIds = ResearchGroupUser::where('research_group_id', $grupoId)
            ->where('rol', '!=', RolGrupoEnum::Director)
            ->pluck('user_id');

        $totalProyectos = Project::whereHas('researchGroups', fn($q) =>
            $q->where('research_groups.id', $grupoId)
        )->count();

        $totalMacros = MacroProject::where('research_group_id', $grupoId)->count();

        return [
            'grupo'                => $grupo,
            'total_investigadores' => $userIds->count(),
            'total_productos'      => GroupProduct::whereIn('author_id', $userIds)->count(),
            'productos_aprobados'  => GroupProduct::whereIn('author_id', $userIds)
                ->where('estado_revision', EstadoRevisionEnum::Aprobado)->count(),
            'total_proyectos'      => $totalProyectos,
            'total_macroproyectos' => $totalMacros,
        ];
    }
}
