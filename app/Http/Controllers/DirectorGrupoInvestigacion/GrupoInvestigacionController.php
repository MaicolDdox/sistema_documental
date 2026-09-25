<?php

namespace App\Http\Controllers\DirectorGrupoInvestigacion;

use App\Http\Controllers\Controller;
use App\Models\GrupoInvestigacion;
use App\Models\ResearchLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * El director completa la información del grupo que administrador_sistema
 * creó con datos básicos (nombre/código/centro) — reforma GDI/SDI.
 */
class GrupoInvestigacionController extends Controller
{
    public function edit()
    {
        $this->authorize('grupos_investigacion.editar');

        $grupo = GrupoInvestigacion::with('lineasInvestigacion')->where('director_id', Auth::id())->firstOrFail();
        $lineasInvestigacion = ResearchLine::where('training_center_id', Auth::user()->training_center_id)->orderBy('nombre')->get();

        return view('director_grupo_investigacion.grupo.edit', compact('grupo', 'lineasInvestigacion'));
    }

    public function update(Request $request)
    {
        $this->authorize('grupos_investigacion.editar');

        $grupo = GrupoInvestigacion::where('director_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'descripcion' => 'nullable|string',
            'lineas_investigacion' => 'nullable|array',
            'lineas_investigacion.*' => [Rule::exists('research_lines', 'id')->where('training_center_id', Auth::user()->training_center_id)],
        ]);

        $grupo->update(['descripcion' => $validated['descripcion'] ?? null]);
        $grupo->lineasInvestigacion()->sync($validated['lineas_investigacion'] ?? []);

        return redirect()->route('director-grupo-investigacion.grupo.edit')
            ->with('success', 'Información del grupo actualizada correctamente.');
    }
}
