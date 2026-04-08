<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\Seedling;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $centerId = $user->training_center_id;

        // Semilleros del centro: los que pertenecen a un grupo de investigación de este centro
        $semillerosQuery = Seedling::with(['leader.person', 'members', 'advisors', 'researchGroup'])
            ->when(
                $centerId,
                fn ($q) => $q->whereHas('researchGroup', fn ($r) => $r->where('training_center_id', $centerId)),
                fn ($q) => $q->whereRaw('0 = 1')
            );

        $semilleros = (clone $semillerosQuery)->get();
        $semillerosActivos = (clone $semillerosQuery)->active()->count();
        $totalSemilleros = $semilleros->count();

        // Líderes: todos los usuarios con rol lider_semillero del mismo centro (igual que en Líderes index)
        $lideresQuery = User::role('lider_semillero')
            ->when(
                $centerId,
                fn ($q) => $q->where('training_center_id', $centerId),
                fn ($q) => $q->whereRaw('0 = 1')
            )
            ->with(['person', 'ledSeedlings']);
        $totalLideres = (clone $lideresQuery)->count();
        $lideresEsteMes = $centerId
            ? (clone $lideresQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count()
            : 0;
        $misLideres = (clone $lideresQuery)->orderBy('email')->take(5)->get();

        $integrantesTotales = $semilleros->sum(fn ($s) => $s->members->count());
        $integrantesNuevos = 0;
        $seedlingIds = $semilleros->pluck('id')->toArray();
        if (!empty($seedlingIds)) {
            $integrantesNuevos = DB::table('seedling_members')
                ->whereIn('seedling_id', $seedlingIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        $asesoresVinculados = 0;
        if (!empty($seedlingIds)) {
            $asesoresVinculados = (int) DB::table('seedling_advisors')
                ->whereIn('seedling_id', $seedlingIds)
                ->selectRaw('count(distinct external_advisor_id) as c')
                ->value('c');
        }

        $misSemilleros = (clone $semillerosQuery)->with(['leader.person', 'members'])->orderBy('nombre')->take(6)->get();

        $chartResumenLabels = collect([
            'Semilleros activos',
            'Líderes',
            'Integrantes',
            'Asesores',
        ]);
        $chartResumenSeries = collect([
            $semillerosActivos,
            $totalLideres,
            $integrantesTotales,
            $asesoresVinculados,
        ]);
        $chartEstadoSemillerosLabels = collect(['Activos', 'Inactivos']);
        $chartEstadoSemillerosSeries = collect([
            $semillerosActivos,
            max($totalSemilleros - $semillerosActivos, 0),
        ]);

        return view('director_semilleros.dashboard', [
            'totalSemilleros'     => $totalSemilleros,
            'semillerosActivos'   => $semillerosActivos,
            'totalLideres'        => $totalLideres,
            'lideresEsteMes'      => $lideresEsteMes,
            'integrantesTotales'  => $integrantesTotales,
            'integrantesNuevos'   => $integrantesNuevos,
            'asesoresVinculados'  => $asesoresVinculados,
            'misSemilleros'       => $misSemilleros,
            'misLideres'          => $misLideres,
            'chartResumenLabels'  => $chartResumenLabels,
            'chartResumenSeries'  => $chartResumenSeries,
            'chartEstadoSemillerosLabels' => $chartEstadoSemillerosLabels,
            'chartEstadoSemillerosSeries' => $chartEstadoSemillerosSeries,
        ]);
    }
}
