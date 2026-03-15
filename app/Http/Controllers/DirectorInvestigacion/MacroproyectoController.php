<?php

namespace App\Http\Controllers\DirectorInvestigacion;

use App\Http\Controllers\Controller;
use App\Models\MacroProject;
use Illuminate\Http\Request;

class MacroproyectoController extends Controller
{
    use DirectorContext;

    public function index(Request $request)
    {
        $grupoId = $this->getGrupoId();

        $query = MacroProject::where('research_group_id', $grupoId)->withCount('projects');

        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('codigo', 'like', "%{$busqueda}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $macroproyectos = $query->orderBy('nombre')->paginate(10)->withQueryString();

        return view('director_investigacion.macroproyectos.index', compact('macroproyectos'));
    }

    public function create()
    {
        $macroproyecto = null;
        return view('director_investigacion.macroproyectos.form', compact('macroproyecto'));
    }

    public function store(Request $request)
    {
        $grupoId = $this->getGrupoId();

        $validated = $request->validate([
            'codigo' => [
                'required', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('macro_projects', 'codigo')
                    ->where('research_group_id', $grupoId)
            ],
            'nombre' => 'required|string|max:255',
            'estado' => 'required|in:activo,inactivo',
        ], [
            'codigo.unique' => 'Este código de macroproyecto ya existe en tu grupo de investigación.',
        ]);

        $validated['research_group_id'] = $grupoId;

        MacroProject::create($validated);

        return redirect()->route('director.macroproyectos.index')
            ->with('success', 'Macroproyecto registrado exitosamente.');
    }

    public function edit(MacroProject $macroproyecto)
    {
        // Verificar que pertenezca al grupo
        if ($macroproyecto->research_group_id !== $this->getGrupoId()) {
            abort(403, 'No tienes permiso para editar este macroproyecto.');
        }

        return view('director_investigacion.macroproyectos.form', compact('macroproyecto'));
    }

    public function update(Request $request, MacroProject $macroproyecto)
    {
        $grupoId = $this->getGrupoId();

        if ($macroproyecto->research_group_id !== $grupoId) {
            abort(403);
        }

        $validated = $request->validate([
            'codigo' => [
                'required', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('macro_projects', 'codigo')
                    ->where('research_group_id', $grupoId)
                    ->ignore($macroproyecto->id)
            ],
            'nombre' => 'required|string|max:255',
            'estado' => 'required|in:activo,inactivo',
        ]);

        $macroproyecto->update($validated);

        return redirect()->route('director.macroproyectos.index')
            ->with('success', 'Macroproyecto actualizado correctamente.');
    }

    public function destroy(MacroProject $macroproyecto)
    {
        if ($macroproyecto->research_group_id !== $this->getGrupoId()) {
            abort(403);
        }

        if ($macroproyecto->projects()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar porque tiene proyectos vinculados. Desactívalo en su lugar.');
        }

        $macroproyecto->delete();

        return redirect()->route('director.macroproyectos.index')
            ->with('success', 'Macroproyecto eliminado.');
    }
}
