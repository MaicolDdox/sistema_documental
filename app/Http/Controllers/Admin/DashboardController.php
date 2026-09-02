<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\Project;
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
     * Panel de administración: métricas, usuarios recientes, proyectos.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $auth */
        $auth = Auth::user();
        $primaryRoleLabel = null;
        if ($auth->roles->isNotEmpty()) {
            $pr = RoleModuleLinks::primaryRole($auth);
            $primaryRoleLabel = $pr ? RoleModuleLinks::labelForRoleName($pr->name) : null;
        }
        $centerId = $auth->training_center_id;
        $super = TrainingCenterAccess::isSuperAdmin($auth);
        $centroAdmin = TrainingCenterAccess::isCentroAdmin($auth);
        $scoped = TrainingCenterAccess::scopedToTrainingCenter($auth);

        // Usuarios: siempre vía TrainingCenterAccess para excluir super_administrador
        // de las métricas/listados cuando quien consulta no es super admin.
        // scopeUserQueryForMetrics() (no scopeUserQueryForList): estos son
        // conteos/listados donde el propio admin sí debe contar como un
        // usuario más de su centro (BUG-20260813-035).
        $userQuery = TrainingCenterAccess::scopeUserQueryForMetrics(User::query(), $auth);

        if ($super) {
            $projectQuery = Project::query();
            $seedlingQuery = Seedling::query();
            $totalCentros = TrainingCenter::activos()->count();
        } elseif ($scoped) {
            $projectQuery = Project::whereHas('seedling', fn ($q) => $q->where('training_center_id', $centerId));
            $seedlingQuery = Seedling::where('training_center_id', $centerId);
            $totalCentros = TrainingCenter::activos()->where('id', $centerId)->count();
        } elseif ($centroAdmin) {
            $projectQuery = Project::query()->where('id', 0);
            $seedlingQuery = Seedling::query()->where('id', 0);
            $totalCentros = 0;
        } else {
            $projectQuery = $centerId
                ? Project::whereHas('seedling', fn ($q) => $q->where('training_center_id', $centerId))
                : Project::query();
            $seedlingQuery = $centerId
                ? Seedling::where('training_center_id', $centerId)
                : Seedling::query();
            $totalCentros = TrainingCenter::activos()->count();
        }

        // Métricas: usuarios
        $totalUsuarios = (clone $userQuery)->count();
        $usuariosEsteMes = (clone $userQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Métricas: proyectos
        $totalProyectos = (clone $projectQuery)->count();
        $proyectosActivos = (clone $projectQuery)->where('estado', EstadoEnum::Activo)->count();

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

        // Proyectos recientes
        $recentProjects = Project::with('seedling')
            ->when($super, fn ($q) => $q)
            ->when(! $super && $scoped, fn ($q) => $q->whereHas('seedling', fn ($s) => $s->where('training_center_id', $centerId)))
            ->when(! $super && ! $scoped && $centroAdmin, fn ($q) => $q->where('id', 0))
            ->when(! $super && ! $scoped && ! $centroAdmin && $centerId, fn ($q) => $q->whereHas('seedling', fn ($s) => $s->where('training_center_id', $centerId)))
            ->latest()
            ->take(4)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'usuariosEsteMes',
            'totalProyectos',
            'proyectosActivos',
            'totalSemilleros',
            'semillerosEsteSemestre',
            'totalCentros',
            'recentUsers',
            'recentProjects',
            'primaryRoleLabel',
        ));
    }
}
