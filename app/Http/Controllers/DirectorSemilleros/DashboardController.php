<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\Seedling;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $centerId = $user->training_center_id;

        // Semilleros del centro — ejecutar la query base UNA sola vez
        $semillerosQuery = Seedling::with(['leader.person', 'members', 'advisors'])
            ->when(
                $centerId,
                fn ($q) => $q->where('training_center_id', $centerId),
                fn ($q) => $q->whereRaw('0 = 1')
            );

        $semilleros = $semillerosQuery->get();
        $semillerosActivos = $semilleros
            ->where('estado', \App\Enums\EstadoEnum::Activo)
            ->count();
        $totalSemilleros = $semilleros->count();

        // Líderes: todos los usuarios con rol lider_semillero del mismo centro — ejecutar UNA sola vez
        $lideresQuery = User::role('lider_semillero')
            ->when(
                $centerId,
                fn ($q) => $q->where('training_center_id', $centerId),
                fn ($q) => $q->whereRaw('0 = 1')
            )
            ->with(['person', 'ledSeedlings']);

        $todosLideres = $lideresQuery->get();
        $totalLideres = $todosLideres->count();
        $lideresEsteMes = $todosLideres
            ->filter(fn ($l) => $l->created_at->month === now()->month && $l->created_at->year === now()->year)
            ->count();
        $misLideres = $todosLideres->sortBy('email')->take(5)->values();

        $integrantesTotales = $semilleros->sum(fn ($s) => $s->members->count());
        $integrantesNuevos = 0;
        $seedlingIds = $semilleros->pluck('id')->toArray();
        if (! empty($seedlingIds)) {
            $integrantesNuevos = DB::table('seedling_members')
                ->whereIn('seedling_id', $seedlingIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        $coinvestigadoresAsociados = 0;
        if (! empty($seedlingIds)) {
            $coinvestigadoresAsociados = (int) DB::table('project_authors')
                ->join('projects', 'projects.id', '=', 'project_authors.project_id')
                ->whereIn('projects.seedling_id', $seedlingIds)
                ->where('project_authors.activo', true)
                ->selectRaw('count(distinct project_authors.user_id) as c')
                ->value('c');
        }

        $misSemilleros = $semilleros->sortBy('nombre')->take(6)->values();

        return view('director_semilleros.dashboard', [
            'totalSemilleros' => $totalSemilleros,
            'semillerosActivos' => $semillerosActivos,
            'totalLideres' => $totalLideres,
            'lideresEsteMes' => $lideresEsteMes,
            'integrantesTotales' => $integrantesTotales,
            'integrantesNuevos' => $integrantesNuevos,
            'coinvestigadoresAsociados' => $coinvestigadoresAsociados,
            'misSemilleros' => $misSemilleros,
            'misLideres' => $misLideres,
        ]);
    }
}
