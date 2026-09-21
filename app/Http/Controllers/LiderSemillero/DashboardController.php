<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Seedling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        /** @var Seedling|null $miSemillero */
        $miSemillero = $user->ledSeedlings()
            ->with(['members.person', 'advisors', 'projects' => fn ($q) => $q->with(['projectEvidences', 'authors'])])
            ->first();

        $metricas = $this->calcularMetricas($user, $miSemillero);
        $productosPendientes = $this->productosPendientesRevision($miSemillero);
        $integrantesSinProyecto = $this->integrantesSinProyectoActivo($miSemillero);
        $proyectosDelSemillero = $this->proyectosDelSemillero($miSemillero);

        return view('lider_semillero.dashboard', [
            'miSemillero' => $miSemillero,
            'metricas' => $metricas,
            'productosPendientes' => $productosPendientes,
            'integrantesSinProyecto' => $integrantesSinProyecto,
            'proyectosDelSemillero' => $proyectosDelSemillero,
        ]);
    }

    private function calcularMetricas($user, ?Seedling $miSemillero): array
    {
        $semillerosACargo = $user->ledSeedlings()->count();
        $nombreSemillero = $miSemillero?->nombre ?? '—';
        $estadoSemillero = $miSemillero && ($miSemillero->estado->value ?? '') === 'activo' ? 'activo' : 'inactivo';

        $integrantes = 0;
        $integrantesNuevosSemestre = 0;
        $productosPendientesCount = 0;
        $proyectosActivosCount = 0;
        $sinProyectoActivoCount = 0;

        if ($miSemillero) {
            $integrantes = $miSemillero->members->count();
            $projectIds = $miSemillero->projects->pluck('id');

            $haceSeisMeses = now()->subMonths(6);
            $integrantesNuevosSemestre = DB::table('seedling_members')
                ->where('seedling_id', $miSemillero->id)
                ->where('created_at', '>=', $haceSeisMeses)
                ->count();

            $productosPendientesCount = $miSemillero->projects
                ->flatMap(fn ($p) => $p->projectEvidences ?? collect())
                ->where('tipo', TipoEvidenciaEnum::ProductoFinal)
                ->where('estado_revision_lider', EstadoRevisionEnum::Pendiente)
                ->count();

            // Activo para tablero: estado activo y no finalizado por fecha.
            $proyectosActivosCount = $miSemillero->projects
                ->where('estado', EstadoEnum::Activo)
                ->filter(function ($p) {
                    return ! $p->fecha_fin || $p->fecha_fin->isSameOrAfter(now()->startOfDay());
                })
                ->count();

            $userIdsConProyectoActivo = $miSemillero->projects
                ->flatMap(fn ($p) => $p->authors ?? collect())
                ->where('activo', true)
                ->pluck('user_id')
                ->unique()
                ->values();
            $memberIds = $miSemillero->members->pluck('id');
            $sinProyectoActivoCount = $memberIds->diff($userIdsConProyectoActivo)->count();
        }

        return [
            'semilleros_a_cargo' => $semillerosACargo,
            'nombre_semillero' => $nombreSemillero,
            'estado_semillero' => $estadoSemillero,
            'integrantes' => $integrantes,
            'integrantes_nuevos_texto' => $integrantesNuevosSemestre > 0 ? "+{$integrantesNuevosSemestre} este semestre" : null,
            'productos_pendientes' => $productosPendientesCount,
            'productos_pendientes_texto' => $productosPendientesCount > 0 ? "+{$productosPendientesCount} requieren revisión" : null,
            'proyectos_activos' => $proyectosActivosCount,
            'proyectos_activos_texto' => $proyectosActivosCount > 0 ? "{$proyectosActivosCount} en ejecución" : 'Sin proyectos en ejecución',
            'sin_proyecto_activo' => $sinProyectoActivoCount,
            'sin_proyecto_texto' => $sinProyectoActivoCount > 0 ? "+{$sinProyectoActivoCount} aprendices sin vincular" : null,
        ];
    }

    private function productosPendientesRevision(?Seedling $miSemillero)
    {
        if (! $miSemillero) {
            return collect();
        }

        return $miSemillero->projects
            ->flatMap(fn ($p) => $p->projectEvidences ?? collect())
            ->where('tipo', TipoEvidenciaEnum::ProductoFinal)
            ->where('estado_revision_lider', EstadoRevisionEnum::Pendiente)
            ->sortByDesc('updated_at')
            ->take(10)
            ->values();
    }

    private function integrantesSinProyectoActivo(?Seedling $miSemillero)
    {
        if (! $miSemillero) {
            return collect();
        }

        $userIdsConProyectoActivo = $miSemillero->projects
            ->flatMap(fn ($p) => $p->authors ?? collect())
            ->where('activo', true)
            ->pluck('user_id')
            ->unique()
            ->values();

        return $miSemillero->members
            ->map(function ($u) use ($userIdsConProyectoActivo) {
                $u->tiene_proyecto_activo = $userIdsConProyectoActivo->contains($u->id);

                return $u;
            })
            ->filter(fn ($u) => ! ($u->tiene_proyecto_activo ?? false))
            ->take(10)
            ->values();
    }

    private function proyectosDelSemillero(?Seedling $miSemillero)
    {
        if (! $miSemillero) {
            return collect();
        }

        return Project::query()
            ->where('seedling_id', $miSemillero->id)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'estado', 'fecha_inicio', 'fecha_fin'])
            ->map(function ($p) {
                $finalizadoPorFecha = $p->fecha_fin && $p->fecha_fin->isBefore(now()->startOfDay());
                $p->estado_tablero = $finalizadoPorFecha ? 'finalizado' : ($p->estado?->value ?? (string) $p->estado);

                return $p;
            });
    }
}
