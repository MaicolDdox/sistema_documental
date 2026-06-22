<?php

namespace App\Http\Controllers\InvestigadorAsociado;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\InvestigationType;
use App\Models\MacroProject;
use App\Models\Project;
use App\Models\ProjectAuthor;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use App\Services\Investigador\ProyectoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProyectoController extends Controller
{
    use InvestigadorContext;

    public function __construct(private ProyectoService $service) {}

    /**
     * Lista los proyectos creados por el investigador autenticado.
     */
    public function index(): View
    {
        $proyectos = Project::with(['researchLine', 'projectAuthors.user.person', 'products', 'projectEvidences'])
            ->where('project_creator_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('investigador.proyectos.index', compact('proyectos'));
    }

    /**
     * Formulario para crear un nuevo proyecto.
     */
    public function create(): View
    {
        $grupoId = $this->getGrupoId();

        return view('investigador.proyectos.form', [
            'proyecto'          => null,
            'lineasInvestigacion' => ResearchLine::orderBy('nombre')->get(),
            'lineasTecnologicas' => TechnologicalLine::orderBy('nombre')->get(),
            'areasTemáticas'    => ThematicArea::orderBy('nombre')->get(),
            'modalidades'       => ProjectModality::orderBy('nombre')->get(),
            'tiposInvestigacion' => InvestigationType::orderBy('nombre')->get(),
            'macroProyectos'    => MacroProject::where('research_group_id', $grupoId)
                ->where('estado', EstadoEnum::Activo)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    /**
     * Almacena el nuevo proyecto.
     */
    public function store(Request $request): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        $validated = $request->validate([
            'nombre'                     => ['required', 'string', 'max:255'],
            'descripcion'               => ['nullable', 'string'],
            'research_line_id'           => ['required', 'exists:research_lines,id'],
            'technological_line_id'      => ['nullable', 'exists:technological_lines,id'],
            'thematic_area_id'           => ['nullable', 'exists:thematic_areas,id'],
            'project_modality_id'        => ['nullable', 'exists:project_modalities,id'],
            'investigation_type_id'      => ['nullable', 'exists:investigation_types,id'],
            'fecha_inicio'               => ['nullable', 'date'],
            'fecha_fin'                  => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'vinculacion_macro_proyecto' => ['boolean'],
            'macro_project_id'           => ['nullable', 'exists:macro_projects,id'],
            'tipo_financiacion'          => ['nullable', 'string', 'max:100'],
        ]);

        $proyecto = $this->service->crear($validated, Auth::id(), $grupoId);

        return redirect()
            ->route('investigador.proyectos.show', $proyecto)
            ->with('success', "Proyecto «{$proyecto->nombre}» creado exitosamente.");
    }

    /**
     * Muestra el detalle de un proyecto.
     */
    public function show(Project $proyecto): View
    {
        $this->authorize('view', $proyecto);

        $proyecto->load([
            'researchLine',
            'projectAuthors.user.person',
            'projectEvidences.uploadedBy',
            'products.groupProducts',
            'macroProject',
        ]);

        return view('investigador.proyectos.show', compact('proyecto'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Project $proyecto): View
    {
        $this->authorize('update', $proyecto);
        $grupoId = $this->getGrupoId();

        return view('investigador.proyectos.form', [
            'proyecto'           => $proyecto,
            'lineasInvestigacion' => ResearchLine::orderBy('nombre')->get(),
            'lineasTecnologicas'  => TechnologicalLine::orderBy('nombre')->get(),
            'areasTemáticas'      => ThematicArea::orderBy('nombre')->get(),
            'modalidades'         => ProjectModality::orderBy('nombre')->get(),
            'tiposInvestigacion'  => InvestigationType::orderBy('nombre')->get(),
            'macroProyectos'     => MacroProject::where('research_group_id', $grupoId)
                ->where('estado', EstadoEnum::Activo)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    /**
     * Actualiza el proyecto.
     */
    public function update(Request $request, Project $proyecto): RedirectResponse
    {
        $this->authorize('update', $proyecto);

        $validated = $request->validate([
            'nombre'                     => ['required', 'string', 'max:255'],
            'descripcion'               => ['nullable', 'string'],
            'research_line_id'           => ['required', 'exists:research_lines,id'],
            'technological_line_id'      => ['nullable', 'exists:technological_lines,id'],
            'thematic_area_id'           => ['nullable', 'exists:thematic_areas,id'],
            'project_modality_id'        => ['nullable', 'exists:project_modalities,id'],
            'investigation_type_id'      => ['nullable', 'exists:investigation_types,id'],
            'fecha_inicio'               => ['nullable', 'date'],
            'fecha_fin'                  => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'vinculacion_macro_proyecto' => ['boolean'],
            'macro_project_id'           => ['nullable', 'exists:macro_projects,id'],
            'tipo_financiacion'          => ['nullable', 'string', 'max:100'],
        ]);

        $this->service->actualizar($proyecto, $validated);

        return redirect()
            ->route('investigador.proyectos.show', $proyecto)
            ->with('success', 'Proyecto actualizado correctamente.');
    }

    /**
     * Elimina un proyecto (solo si no tiene productos).
     */
    public function destroy(Project $proyecto): RedirectResponse
    {
        $this->authorize('delete', $proyecto);

        if (! $this->service->puedeEliminar($proyecto)) {
            return back()->withErrors(['error' => 'No puedes eliminar un proyecto que tiene productos registrados.']);
        }

        $nombre = $proyecto->nombre;
        $proyecto->delete();

        return redirect()
            ->route('investigador.proyectos.index')
            ->with('success', "Proyecto «{$nombre}» eliminado.");
    }

    /**
     * Retorna los autores del proyecto en JSON (para el formulario de producto).
     */
    public function autores(Project $proyecto): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $proyecto);

        $autores = ProjectAuthor::with('user.person')
            ->where('project_id', $proyecto->id)
            ->get()
            ->map(fn($pa) => [
                'id'     => $pa->user_id,
                'nombre' => optional($pa->user->person)->nombre_completo ?? $pa->user->email,
            ]);

        return response()->json($autores);
    }

    /**
     * Finaliza el proyecto estableciendo la fecha_fin a hoy.
     * Esto lo hace elegible para convertirse en producto del grupo.
     */
    public function finalizar(Project $proyecto): RedirectResponse
    {
        $this->authorize('update', $proyecto);

        if ($proyecto->fecha_fin && $proyecto->fecha_fin <= now()) {
            return back()->with('success', 'El proyecto ya estaba marcado como finalizado.');
        }

        $proyecto->update(['fecha_fin' => now()->toDateString()]);

        return back()->with('success', "¡Proyecto «{$proyecto->nombre}» finalizado. Cuando quieras, regístralo como producto final del grupo.");
    }
}
