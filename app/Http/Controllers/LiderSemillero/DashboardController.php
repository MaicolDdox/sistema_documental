<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Enums\EstadoRevisionEnum;
use App\Http\Controllers\Controller;
use App\Models\GroupProduct;
use App\Models\Project;
use App\Models\ProjectAuthor;
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
            ->with(['researchGroup', 'members.person', 'advisors'])
            ->first();

        $metricas = $this->calcularMetricas($user, $miSemillero);
        $productosPendientes = $this->productosPendientesRevision($miSemillero);
        $integrantesSinProyecto = $this->integrantesSinProyectoActivo($miSemillero);

        return view('lider_semillero.dashboard', [
            'miSemillero'             => $miSemillero,
            'metricas'                => $metricas,
            'productosPendientes'     => $productosPendientes,
            'integrantesSinProyecto'  => $integrantesSinProyecto,
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
        $projectIds = [];

        if ($miSemillero) {
            $integrantes = $miSemillero->members()->count();
            $projectIds = DB::table('project_seedlings')
                ->where('seedling_id', $miSemillero->id)
                ->pluck('project_id');

            $haceSeisMeses = now()->subMonths(6);
            $integrantesNuevosSemestre = DB::table('seedling_members')
                ->where('seedling_id', $miSemillero->id)
                ->where('created_at', '>=', $haceSeisMeses)
                ->count();

            $productIds = DB::table('products')->whereIn('project_id', $projectIds)->pluck('id');
            $productosPendientesCount = GroupProduct::whereIn('product_id', $productIds)
                ->whereIn('estado_revision', [EstadoRevisionEnum::Pendiente, EstadoRevisionEnum::EnRevision])
                ->count();

            $proyectosActivosCount = Project::whereIn('id', $projectIds)
                ->active()
                ->count();

            $userIdsConProyectoActivo = ProjectAuthor::whereIn('project_id', $projectIds)
                ->where('activo', true)
                ->pluck('user_id')
                ->unique()
                ->values();
            $memberIds = $miSemillero->members()->pluck('users.id');
            $sinProyectoActivoCount = $memberIds->diff($userIdsConProyectoActivo)->count();
        }

        return [
            'semilleros_a_cargo'       => $semillerosACargo,
            'nombre_semillero'          => $nombreSemillero,
            'estado_semillero'         => $estadoSemillero,
            'integrantes'               => $integrantes,
            'integrantes_nuevos_texto'  => $integrantesNuevosSemestre > 0 ? "+{$integrantesNuevosSemestre} este semestre" : null,
            'productos_pendientes'     => $productosPendientesCount,
            'productos_pendientes_texto' => $productosPendientesCount > 0 ? "+{$productosPendientesCount} requieren revisión" : null,
            'proyectos_activos'         => $proyectosActivosCount,
            'proyectos_activos_texto'   => $proyectosActivosCount > 0 ? "+{$proyectosActivosCount} activo(s)" : null,
            'sin_proyecto_activo'        => $sinProyectoActivoCount,
            'sin_proyecto_texto'        => $sinProyectoActivoCount > 0 ? "+{$sinProyectoActivoCount} aprendices sin vincular" : null,
        ];
    }

    private function productosPendientesRevision(?Seedling $miSemillero)
    {
        if (!$miSemillero) {
            return collect();
        }
        $projectIds = DB::table('project_seedlings')->where('seedling_id', $miSemillero->id)->pluck('project_id');
        $productIds = DB::table('products')->whereIn('project_id', $projectIds)->pluck('id');

        return GroupProduct::with(['author.person', 'product', 'mincienciasTypology'])
            ->whereIn('product_id', $productIds)
            ->whereIn('estado_revision', [EstadoRevisionEnum::Pendiente, EstadoRevisionEnum::EnRevision])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
    }

    private function integrantesSinProyectoActivo(?Seedling $miSemillero)
    {
        if (!$miSemillero) {
            return collect();
        }
        $projectIds = DB::table('project_seedlings')->where('seedling_id', $miSemillero->id)->pluck('project_id');
        $userIdsConProyectoActivo = ProjectAuthor::whereIn('project_id', $projectIds)
            ->where('activo', true)
            ->pluck('user_id')
            ->unique()
            ->values();

        return $miSemillero->members()
            ->with('person')
            ->get()
            ->map(function ($u) use ($userIdsConProyectoActivo) {
                $u->tiene_proyecto_activo = $userIdsConProyectoActivo->contains($u->id);
                return $u;
            })
            ->take(10)
            ->values();
    }
}
