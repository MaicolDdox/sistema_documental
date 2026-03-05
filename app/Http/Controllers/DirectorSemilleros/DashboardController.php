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

        $semillerosQuery = Seedling::with(['leader.person', 'members', 'advisors', 'researchGroup'])
            ->when($centerId, function ($q) use ($centerId) {
                $q->whereHas('researchGroup', fn ($r) => $r->where('training_center_id', $centerId));
            });

        $semilleros = (clone $semillerosQuery)->get();
        $semillerosActivos = (clone $semillerosQuery)->active()->count();
        $totalSemilleros = $semilleros->count();

        $lideresIds = $semilleros->pluck('leader_id')->filter()->unique()->values();
        $totalLideres = $lideresIds->count();
        $lideresEsteMes = $totalLideres > 0
            ? User::whereIn('id', $lideresIds)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count()
            : 0;

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
        $misLideres = $lideresIds->isNotEmpty()
            ? User::with('person')->whereIn('id', $lideresIds)->take(5)->get()
            : collect();

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
        ]);
    }
}
