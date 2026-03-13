<?php

namespace App\Http\Controllers\AsesorSemillero;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\AsesorSemillero\StoreProyectoRequest;
use App\Models\InvestigationType;
use App\Models\MacroProject;
use App\Models\Project;
use App\Models\ProjectAuthor;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProyectoController extends Controller
{
    /**
     * Obtiene el semillero del asesor autenticado.
     */
    private function getSemilleroDelAsesor(): ?Seedling
    {
        return Seedling::whereHas('advisors', function ($q) {
            $q->where('external_advisors.user_id', Auth::id())
              ->where('seedling_advisors.activo', true);
        })->first();
    }

    /**
     * Obtiene los IDs de proyectos del semillero del asesor.
     */
    private function getProjectIdsDelSemillero(int $seedlingId): \Illuminate\Support\Collection
    {
        return DB::table('project_seedlings')
            ->where('seedling_id', $seedlingId)
            ->pluck('project_id');
    }

    /**
     * Permiso: proyectos.listar_semillero
     * Lista proyectos del semillero del asesor.
     */
    public function index(Request $request): View
    {
        $semillero = $this->getSemilleroDelAsesor();
        $proyectos = collect();

        if ($semillero) {
            $projectIds = $this->getProjectIdsDelSemillero($semillero->id);

            $query = Project::with([
                'researchLine',
                'technologicalLine',
                'thematicArea',
                'projectModality',
                'investigationType',
                'projectAuthors',
                'products',
                'macroProject',
            ])->whereIn('id', $projectIds);

            if ($request->filled('buscar')) {
                $query->where('nombre', 'like', '%' . $request->buscar . '%');
            }

            $proyectos = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        }

        return view('asesor_semillero.proyectos.index', compact('semillero', 'proyectos'));
    }

    /**
     * Permiso: proyectos.crear_semillero
     * Muestra formulario de creación de proyecto.
     */
    public function create(): View
    {
        $semillero        = $this->getSemilleroDelAsesor();
        $lineasInves      = ResearchLine::orderBy('nombre')->get();
        $lineasTec        = TechnologicalLine::orderBy('nombre')->get();
        $areasTematicas   = ThematicArea::orderBy('nombre')->get();
        $modalidades      = ProjectModality::orderBy('nombre')->get();
        $tiposInves       = InvestigationType::orderBy('nombre')->get();
        $macroProyectos   = MacroProject::where('research_group_id', $semillero->research_group_id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('asesor_semillero.proyectos.create', compact(
            'semillero', 'lineasInves', 'lineasTec', 'areasTematicas', 'modalidades', 'tiposInves', 'macroProyectos'
        ));
    }

    /**
     * Permiso: proyectos.crear_semillero
     * Crea el proyecto y lo vincula automáticamente al semillero + agrega al asesor como autor.
     */
    public function store(StoreProyectoRequest $request): RedirectResponse
    {
        $semillero = $this->getSemilleroDelAsesor();
        if (!$semillero) {
            return redirect()->back()->with('error', 'No tienes un semillero asignado.');
        }

        $validated = $request->validated();

        if ($validated['tiene_macroproyecto']) {
            $request->validate([
                'macro_project_id' => [
                    'required',
                    function ($attribute, $value, $fail) use ($semillero) {
                        $exists = MacroProject::where('id', $value)
                            ->where('research_group_id', $semillero->research_group_id)
                            ->exists();
                        if (!$exists) {
                            $fail('El macroproyecto seleccionado no es válido para el grupo de investigación de este semillero.');
                        }
                    },
                ],
            ]);
        }

        DB::transaction(function () use ($validated, $semillero, $request) {
            // 1. Crear el proyecto
            $proyecto = Project::create([
                'project_creator_id'    => Auth::id(),
                'research_line_id'      => $validated['research_line_id'],
                'technological_line_id' => $validated['technological_line_id'] ?? null,
                'thematic_area_id'      => $validated['thematic_area_id'] ?? null,
                'project_modality_id'   => $validated['project_modality_id'],
                'investigation_type_id' => $validated['investigation_type_id'],
                'nombre'                => $validated['nombre'],
                'descripccion'          => $validated['descripccion'] ?? null,
                'fecha_inicio'          => $validated['fecha_inicio'],
                'fecha_fin'             => $validated['fecha_fin'] ?? null,
                'estado'                => EstadoEnum::Activo,
                'vinculacion_macro_proyecto' => (bool) $validated['tiene_macroproyecto'],
                'macro_project_id'      => $validated['tiene_macroproyecto'] ? $request->macro_project_id : null,
            ]);

            // 2. Vincular proyecto al semillero
            DB::table('project_seedlings')->insert([
                'project_id'  => $proyecto->id,
                'seedling_id' => $semillero->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // 3. Agregar al asesor como primer autor
            ProjectAuthor::create([
                'project_id' => $proyecto->id,
                'user_id'    => Auth::id(),
                'activo'     => true,
            ]);
        });

        return redirect()->route('asesor.proyectos.index')
            ->with('success', 'Proyecto creado correctamente y vinculado a tu semillero.');
    }

    /**
     * Permiso: proyectos.ver_detalle
     * Muestra el detalle de un proyecto.
     */
    public function show(int $id): View
    {
        $semillero = $this->getSemilleroDelAsesor();
        $proyecto  = $this->findProyectoDelSemillero($id, $semillero);

        $autores   = ProjectAuthor::with('user.person')->where('project_id', $id)->where('activo', true)->get();
        $productos = $proyecto->products()->with('groupProducts.mincienciasTypology')->get();
        $evidencias = $proyecto->projectEvidences()->latest()->get();
        $macro     = $proyecto->macroProject;

        return view('asesor_semillero.proyectos.show', compact(
            'proyecto', 'autores', 'productos', 'evidencias', 'macro', 'semillero'
        ));
    }

    /**
     * Permiso: proyectos.editar
     * Muestra formulario de edición del proyecto.
     */
    public function edit(int $id): View
    {
        $semillero      = $this->getSemilleroDelAsesor();
        $proyecto       = $this->findProyectoDelSemillero($id, $semillero);
        $lineasInves    = ResearchLine::orderBy('nombre')->get();
        $lineasTec      = TechnologicalLine::orderBy('nombre')->get();
        $areasTematicas = ThematicArea::orderBy('nombre')->get();
        $modalidades    = ProjectModality::orderBy('nombre')->get();
        $macroProyectos = MacroProject::where('research_group_id', $semillero->research_group_id)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('asesor_semillero.proyectos.edit', compact(
            'proyecto', 'lineasInves', 'lineasTec', 'areasTematicas', 'modalidades', 'macroProyectos'
        ));
    }

    /**
     * Permiso: proyectos.editar
     * Actualiza el proyecto. El tipo de investigación NO puede modificarse.
     */
    public function update(StoreProyectoRequest $request, int $id): RedirectResponse
    {
        $semillero = $this->getSemilleroDelAsesor();
        $proyecto  = $this->findProyectoDelSemillero($id, $semillero);
        $validated = $request->validated();

        DB::transaction(function () use ($proyecto, $validated, $semillero, $request) {
            // Actualizar proyecto — SE IGNORA investigation_type_id (bloqueado post-creación)
            $proyecto->update([
                'research_line_id'      => $validated['research_line_id'],
                'technological_line_id' => $validated['technological_line_id'] ?? null,
                'thematic_area_id'      => $validated['thematic_area_id'] ?? null,
                'project_modality_id'   => $validated['project_modality_id'],
                'nombre'                => $validated['nombre'],
                'descripccion'          => $validated['descripccion'] ?? null,
                'fecha_inicio'          => $validated['fecha_inicio'],
                'fecha_fin'             => $validated['fecha_fin'] ?? null,
                'vinculacion_macro_proyecto' => (bool) $validated['tiene_macroproyecto'],
                'macro_project_id'      => $validated['tiene_macroproyecto'] ? $request->macro_project_id : null,
            ]);
        });

        return redirect()->route('asesor.proyectos.show', $proyecto->id)
            ->with('success', 'Proyecto actualizado correctamente.');
    }

    /**
     * Permiso: proyectos.vincular_integrantes
     * Vista de gestión de integrantes del proyecto.
     */
    public function integrantes(int $id): View
    {
        $semillero = $this->getSemilleroDelAsesor();
        $proyecto  = $this->findProyectoDelSemillero($id, $semillero);

        // Autores actuales del proyecto
        $autoresActuales = ProjectAuthor::with('user.person')
            ->where('project_id', $id)
            ->where('activo', true)
            ->get();

        $autoresIds = $autoresActuales->pluck('user_id')->toArray();

        // Miembros del semillero que NO están en el proyecto
        $disponibles = $semillero->members()
            ->with('person')
            ->whereNotIn('users.id', $autoresIds)
            ->where('users.id', '!=', Auth::id())
            ->get();

        // Alerta: miembros del semillero sin ningún proyecto
        $projectIds = $this->getProjectIdsDelSemillero($semillero->id);
        $miembrosConProyecto = ProjectAuthor::whereIn('project_id', $projectIds)
            ->where('activo', true)
            ->pluck('user_id');
        $miembrosSinProyecto = $semillero->members()
            ->with('person')
            ->whereNotIn('users.id', $miembrosConProyecto)
            ->where('users.id', '!=', Auth::id())
            ->get();

        return view('asesor_semillero.proyectos.integrantes', compact(
            'proyecto', 'autoresActuales', 'disponibles', 'miembrosSinProyecto', 'semillero'
        ));
    }

    /**
     * Permiso: proyectos.vincular_integrantes
     * Vincula un aprendiz como autor del proyecto.
     */
    public function vincularIntegrante(Request $request, int $id): RedirectResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $semillero = $this->getSemilleroDelAsesor();
        $proyecto  = $this->findProyectoDelSemillero($id, $semillero);

        // Verificar que el usuario es miembro del semillero
        if (!$semillero->members()->where('users.id', $request->user_id)->exists()) {
            return redirect()->back()->with('error', 'El usuario no pertenece a tu semillero.');
        }

        $pa = ProjectAuthor::firstOrCreate(
            ['project_id' => $proyecto->id, 'user_id' => $request->user_id],
            ['activo' => true]
        );
        $pa->update(['activo' => true]);

        return redirect()->back()->with('success', 'Integrante vinculado al proyecto correctamente.');
    }

    /**
     * Permiso: proyectos.vincular_integrantes
     * Desvincula un aprendiz del proyecto (marca activo = false).
     */
    public function desvincularIntegrante(Request $request, int $id, int $user_id): RedirectResponse
    {
        $semillero = $this->getSemilleroDelAsesor();
        $this->findProyectoDelSemillero($id, $semillero);

        if ($user_id === Auth::id()) {
            return redirect()->back()->with('error', 'No puedes desvincularte a ti mismo del proyecto.');
        }

        ProjectAuthor::where('project_id', $id)
            ->where('user_id', $user_id)
            ->update(['activo' => false]);

        return redirect()->back()->with('success', 'Integrante desvinculado del proyecto.');
    }

    /**
     * Busca un proyecto verificando que pertenezca al semillero del asesor.
     */
    private function findProyectoDelSemillero(int $projectId, ?Seedling $semillero): Project
    {
        if (!$semillero) {
            abort(403, 'No tienes un semillero asignado.');
        }

        $projectIds = $this->getProjectIdsDelSemillero($semillero->id);

        if (!$projectIds->contains($projectId)) {
            abort(403, 'Este proyecto no pertenece a tu semillero.');
        }

        return Project::with([
            'researchLine', 'technologicalLine', 'thematicArea',
            'projectModality', 'investigationType',
        ])->findOrFail($projectId);
    }
}
