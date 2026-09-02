<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seedling;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Vista de solo lectura para administrador_sistema: semilleros del centro,
 * su líder, proyectos, líder de proyecto, integrantes y co-investigadores
 * vinculados. Sin edición ni eliminación — esa responsabilidad es de quien
 * crea cada registro (director_semilleros / lider_semillero / lider_proyecto).
 */
class SemilleroController extends Controller
{
    public function index(): View
    {
        $this->authorize('semilleros.listar');

        $centerId = Auth::user()->training_center_id;

        $semilleros = Seedling::with(['leader.person'])
            ->withCount('projects')
            ->when(
                $centerId,
                fn ($q) => $q->where('training_center_id', $centerId),
                fn ($q) => $q->whereRaw('0 = 1')
            )
            ->orderBy('nombre')
            ->get();

        return view('admin.semilleros.index', compact('semilleros'));
    }

    public function show(Seedling $semillero): View
    {
        $this->authorize('semilleros.ver_detalle');
        $this->ensureDelCentro($semillero);

        $semillero->load([
            'leader.person',
            'projects.liderProyecto.person',
            'projects.learners',
            'projects.authors' => fn ($q) => $q->wherePivot('activo', true),
        ]);

        return view('admin.semilleros.show', compact('semillero'));
    }

    private function ensureDelCentro(Seedling $semillero): void
    {
        $centerId = Auth::user()->training_center_id;
        if ((int) $semillero->training_center_id !== (int) $centerId) {
            abort(403, 'Este semillero no pertenece a tu centro de formación.');
        }
    }
}
