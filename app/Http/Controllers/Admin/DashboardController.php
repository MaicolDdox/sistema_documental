<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResearchGroup;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Panel de administración: métricas, usuarios recientes, grupos de investigación.
     */
    public function index(Request $request): View
    {
        $centerId = Auth::user()->training_center_id;

        $userQuery = $centerId ? User::where('training_center_id', $centerId) : User::query();
        $groupQuery = $centerId ? ResearchGroup::where('training_center_id', $centerId) : ResearchGroup::query();
        $seedlingQuery = $centerId
            ? Seedling::whereHas('researchGroup', fn ($q) => $q->where('training_center_id', $centerId))
            : Seedling::query();

        // Métricas: usuarios
        $totalUsuarios = (clone $userQuery)->count();
        $usuariosEsteMes = (clone $userQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Métricas: grupos de investigación
        $totalGrupos = (clone $groupQuery)->count();
        $gruposActivos = (clone $groupQuery)->where('estado', 'activo')->count();

        // Métricas: semilleros (seedlings de grupos del centro)
        $totalSemilleros = (clone $seedlingQuery)->count();
        $semestreInicio = now()->month <= 6 ? 1 : 7;
        $semestreFin = now()->month <= 6 ? 6 : 12;
        $semillerosEsteSemestre = (clone $seedlingQuery)
            ->whereMonth('created_at', '>=', $semestreInicio)
            ->whereMonth('created_at', '<=', $semestreFin)
            ->whereYear('created_at', now()->year)
            ->count();

        // Métricas: solo centros activos (los desactivados no cuentan en el sistema)
        $totalCentros = TrainingCenter::activos()->count();

        // Usuarios recientes (últimas cuentas creadas)
        $recentUsers = User::with(['person', 'roles'])
            ->when($centerId, fn ($q) => $q->where('training_center_id', $centerId))
            ->latest()
            ->take(4)
            ->get();

        // Grupos de investigación recientes
        $recentResearchGroups = ResearchGroup::with('trainingCenter')
            ->when($centerId, fn ($q) => $q->where('training_center_id', $centerId))
            ->latest()
            ->take(4)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'usuariosEsteMes',
            'totalGrupos',
            'gruposActivos',
            'totalSemilleros',
            'semillerosEsteSemestre',
            'totalCentros',
            'recentUsers',
            'recentResearchGroups'
        ));
    }
}
