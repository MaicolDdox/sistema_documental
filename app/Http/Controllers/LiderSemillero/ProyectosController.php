<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProyectosController extends Controller
{
    /**
     * Lista los proyectos vinculados al semillero que lidera el usuario.
     */
    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();

        $proyectos = collect();
        if ($semillero) {
            $projectIds = DB::table('project_seedlings')
                ->where('seedling_id', $semillero->id)
                ->pluck('project_id');

            $proyectos = Project::with(['projectCreator.person'])
                ->withCount(['projectAuthors as integrantes_count', 'products as productos_count'])
                ->whereIn('id', $projectIds)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($project) {
                    $project->avance = $this->calcularAvance($project);
                    $project->avance_label = $this->etiquetaAvance($project->avance);
                    return $project;
                });
        }

        return view('lider_semillero.proyectos.index', [
            'semillero'  => $semillero,
            'proyectos'  => $proyectos,
        ]);
    }

    /**
     * Calcula avance del proyecto (0-100). Por ahora basado en fechas o placeholder.
     */
    private function calcularAvance(Project $project): int
    {
        if (!$project->fecha_inicio) {
            return 0;
        }
        $inicio = $project->fecha_inicio->startOfDay();
        $fin = $project->fecha_fin ? $project->fecha_fin->endOfDay() : now()->addYear();
        $total = $inicio->diffInDays($fin);
        if ($total <= 0) {
            return 100;
        }
        $transcurrido = $inicio->diffInDays(now(), false);
        if ($transcurrido <= 0) {
            return 0;
        }
        $pct = (int) round(($transcurrido / $total) * 100);
        return min(100, max(0, $pct));
    }

    /**
     * Etiqueta visual para el avance calculado.
     */
    private function etiquetaAvance(int $avance): string
    {
        if ($avance >= 100) {
            return 'Finalizado';
        }
        if ($avance >= 75) {
            return 'Alto';
        }
        if ($avance >= 40) {
            return 'Medio';
        }
        if ($avance > 0) {
            return 'Inicial';
        }

        return 'Sin iniciar';
    }
}
