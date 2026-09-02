<?php

namespace App\Http\Controllers\LiderProyecto;

use App\Enums\TipoEvidenciaEnum;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LiderProyectoContext;

    public function index(): View
    {
        $proyecto = $this->miProyecto()->load([
            'seedling',
            'researchLine',
            'technologicalLine',
            'thematicArea',
            'projectModality',
            'investigationType',
            'learners',
            'authors' => fn ($q) => $q->wherePivot('activo', true),
        ]);

        $ultimaEvidenciaProducto = $proyecto->projectEvidences()
            ->where('tipo', TipoEvidenciaEnum::ProductoFinal)
            ->latest()
            ->first();

        // Punto rojo del sidebar: al visitar el dashboard queda "visto".
        $proyecto->projectEvidences()
            ->where('tipo', TipoEvidenciaEnum::ProductoFinal)
            ->whereNull('visto_por_lider_proyecto_at')
            ->update(['visto_por_lider_proyecto_at' => now()]);

        return view('lider_proyecto.dashboard', compact('proyecto', 'ultimaEvidenciaProducto'));
    }
}
