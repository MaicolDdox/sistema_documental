<?php

namespace App\Http\Controllers\LiderProyecto;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

/**
 * Resuelve el proyecto asignado al Líder de Proyecto autenticado.
 * Cada Líder de Proyecto solo ve/gestiona el proyecto al que está
 * vinculado (projects.lider_proyecto_user_id) — nunca proyectos ajenos.
 */
trait LiderProyectoContext
{
    protected function miProyecto(): Project
    {
        $proyecto = Project::where('lider_proyecto_user_id', Auth::id())->first();

        if ($proyecto === null) {
            abort(404, 'No tienes un proyecto asignado como Líder de Proyecto. Contacta a tu Director de Semilleros.');
        }

        return $proyecto;
    }
}
