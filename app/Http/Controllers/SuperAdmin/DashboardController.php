<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Panel global: métricas de toda la instancia (sin filtrar por centro).
     */
    public function index(Request $request): View
    {
        $totalUsuarios = User::query()->count();
        $usuariosEsteMes = User::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalLideresProyecto = User::role('lider_proyecto')->count();
        $proyectosActivos = Project::query()->where('estado', EstadoEnum::Activo)->count();

        $totalSemilleros = Seedling::query()->count();
        $semestreInicio = now()->month <= 6 ? 1 : 7;
        $semestreFin = now()->month <= 6 ? 6 : 12;
        $semillerosEsteSemestre = Seedling::query()
            ->whereMonth('created_at', '>=', $semestreInicio)
            ->whereMonth('created_at', '<=', $semestreFin)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalCentrosActivos = TrainingCenter::activos()->count();
        $totalCentros = TrainingCenter::query()->count();

        $totalProyectos = Project::query()->count();

        $recentUsers = User::with(['person', 'roles', 'trainingCenter'])
            ->latest()
            ->take(6)
            ->get();

        $recentProjects = Project::with('seedling.trainingCenter')
            ->latest()
            ->take(5)
            ->get();

        return view('super-admin.dashboard', compact(
            'totalUsuarios',
            'usuariosEsteMes',
            'totalLideresProyecto',
            'proyectosActivos',
            'totalSemilleros',
            'semillerosEsteSemestre',
            'totalCentrosActivos',
            'totalCentros',
            'totalProyectos',
            'recentUsers',
            'recentProjects'
        ));
    }
}
