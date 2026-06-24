<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\ResearchGroup;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\RoleModuleLinks;
use App\Support\TrainingCenterAccess;
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
        /** @var \App\Models\User $auth */
        $auth = Auth::user();
        $primaryRoleLabel = null;
        $roleModuleNav = null;
        if ($auth->roles->isNotEmpty()) {
            $pr = RoleModuleLinks::primaryRole($auth);
            $primaryRoleLabel = $pr ? RoleModuleLinks::labelForRoleName($pr->name) : null;
            if ($auth->roles->count() > 1) {
                $roleModuleNav = RoleModuleLinks::moduleLinksWithPrimary($auth);
            }
        }
        $centerId = $auth->training_center_id;
        $super = TrainingCenterAccess::isSuperAdmin($auth);
        $centroAdmin = TrainingCenterAccess::isCentroAdmin($auth);
        $scoped = TrainingCenterAccess::scopedToTrainingCenter($auth);

        // Usuarios: siempre vía TrainingCenterAccess para excluir super_administrador
        // de las métricas/listados cuando quien consulta no es super admin.
        $userQuery = TrainingCenterAccess::scopeUserQueryForList(User::query(), $auth);

        if ($super) {
            $groupQuery = ResearchGroup::query();
            $seedlingQuery = Seedling::query();
            $totalCentros = TrainingCenter::activos()->count();
        } elseif ($scoped) {
            $groupQuery = ResearchGroup::query()->where('training_center_id', $centerId);
            $seedlingQuery = Seedling::whereHas('researchGroup', fn ($q) => $q->where('training_center_id', $centerId));
            $totalCentros = TrainingCenter::activos()->where('id', $centerId)->count();
        } elseif ($centroAdmin) {
            $groupQuery = ResearchGroup::query()->where('id', 0);
            $seedlingQuery = Seedling::query()->where('id', 0);
            $totalCentros = 0;
        } else {
            $groupQuery = $centerId ? ResearchGroup::where('training_center_id', $centerId) : ResearchGroup::query();
            $seedlingQuery = $centerId
                ? Seedling::whereHas('researchGroup', fn ($q) => $q->where('training_center_id', $centerId))
                : Seedling::query();
            $totalCentros = TrainingCenter::activos()->count();
        }

        // Métricas: usuarios
        $totalUsuarios = (clone $userQuery)->count();
        $usuariosEsteMes = (clone $userQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Métricas: grupos de investigación
        $totalGrupos = (clone $groupQuery)->count();
        $gruposActivos = (clone $groupQuery)->where('estado', EstadoEnum::Activo)->count();

        // Métricas: semilleros (seedlings de grupos del centro)
        $totalSemilleros = (clone $seedlingQuery)->count();
        $semestreInicio = now()->month <= 6 ? 1 : 7;
        $semestreFin = now()->month <= 6 ? 6 : 12;
        $semillerosEsteSemestre = (clone $seedlingQuery)
            ->whereMonth('created_at', '>=', $semestreInicio)
            ->whereMonth('created_at', '<=', $semestreFin)
            ->whereYear('created_at', now()->year)
            ->count();

        // Usuarios recientes (últimas cuentas creadas)
        $recentUsers = TrainingCenterAccess::scopeUserQueryForList(User::with(['person', 'roles']), $auth)
            ->latest()
            ->take(4)
            ->get();

        // Grupos de investigación recientes
        $recentResearchGroups = ResearchGroup::with('trainingCenter')
            ->when($super, fn ($q) => $q)
            ->when(! $super && $scoped, fn ($q) => $q->where('training_center_id', $centerId))
            ->when(! $super && ! $scoped && $centroAdmin, fn ($q) => $q->where('id', 0))
            ->when(! $super && ! $scoped && ! $centroAdmin && $centerId, fn ($q) => $q->where('training_center_id', $centerId))
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
            'recentResearchGroups',
            'primaryRoleLabel',
            'roleModuleNav'
        ));
    }
}
