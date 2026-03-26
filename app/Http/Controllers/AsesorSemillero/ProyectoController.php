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
     * Obtiene todos los semilleros del asesor autenticado con research_group_id.
     */
    private function getSemillerosDelAsesor(): \Illuminate\Database\Eloquent\Collection
    {
        $advisor = \App\Models\ExternalAdvisor::where('user_id', Auth::id())->first();
        if (!$advisor) return collect();

        return Seedling::whereHas('seedlingAdvisors', function ($q) use ($advisor) {
            $q->where('external_advisor_id', $advisor->id)
              ->where('activo', true);
        })->get(['id', 'nombre', 'research_group_id']);
    }

    /**
     * Obtiene los IDs de los proyectos de todos los semilleros del asesor.
     */
    private function getAllProjectIdsDelAsesor(): \Illuminate\Support\Collection
    {
        $semilleroIds = $this->getSemillerosDelAsesor()->pluck('id');
        return DB::table('project_seedlings')
            ->whereIn('seedling_id', $semilleroIds)
            ->pluck('project_id')
            ->unique();
    }

    /**
     * Busca un proyecto verificando que pertenezca a algún semillero del asesor.
     */
    private function findProyectoDelSemillero(int $projectId): Project
    {
        $projectIds = $this->getAllProjectIdsDelAsesor();

        if (!$projectIds->contains($projectId)) {
            abort(403, 'Este proyecto no pertenece a tus semilleros.');
        }

        return Project::findOrFail($projectId);
    }

    /**
     * Permiso: proyectos.listar_semillero
     * Lista proyectos de todos los semilleros del asesor.
     */
    public function index(Request $request): View
    {
        $semilleros = $this->getSemillerosDelAsesor();
        $proyectos  = collect();

        if ($semilleros->isNotEmpty()) {
            $projectIds = $this->getAllProjectIdsDelAsesor();

            $query = Project::with([
                'researchLine',
                'technologicalLine',
                'thematicArea',
                'projectModality',
                'investigationType',
                'projectAuthors',
                'products',
                'macroProject',
                'seedlings'
            ])->whereIn('id', $projectIds);

            if ($request->filled('buscar')) {
                $query->where('nombre', 'like', '%' . $request->buscar . '%');
            }

            $proyectos = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        }

        return view('asesor_semillero.proyectos.index', compact('semilleros', 'proyectos'));
    }

    /**
     * Permiso: proyectos.crear_semillero
     * Muestra formulario de creación de proyecto.
     */
    public function create(): View
    {
        $semilleros       = $this->getSemillerosDelAsesor();
        $lineasInves      = ResearchLine::orderBy('nombre')->get();
        $lineasTec        = TechnologicalLine::orderBy('nombre')->get();
        $areasTematicas   = ThematicArea::orderBy('nombre')->get();
        $modalidades      = ProjectModality::orderBy('nombre')->get();
        $tiposInves       = InvestigationType::orderBy('nombre')->get();
        
        $group_ids        = $semilleros->pluck('research_group_id')->filter()->unique();
        $macroProyectos   = MacroProject::whereIn('research_group_id', $group_ids)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return view('asesor_semillero.proyectos.create', compact(
            'semilleros', 'lineasInves', 'lineasTec', 'areasTematicas', 'modalidades', 'tiposInves', 'macroProyectos'
        ));
    }

    /**
     * Permiso: proyectos.crear_semillero
     * Crea el proyecto y lo vincula automáticamente al semillero + agrega al asesor como autor.
     */
    public function store(StoreProyectoRequest $request): RedirectResponse
    {
        $semilleros = $this->getSemillerosDelAsesor();
        if ($semilleros->isEmpty()) {
            return redirect()->back()->with('error', 'No tienes un semillero asignado.');
        }

        $validated = $request->validated();
        
        // El semillero_id viene validado, buscamos su research_group_id
        $selectedSeedling = $semilleros->firstWhere('id', $validated['seedling_id']);
        if (!$selectedSeedling) {
            return redirect()->back()->with('error', 'Semillero inválido.');
        }

        if ($validated['tiene_macroproyecto']) {
            $request->validate([
                'macro_project_id' => [
                    'required',
                    function ($attribute, $value, $fail) use ($selectedSeedling) {
                        $exists = MacroProject::where('id', $value)
                            ->where('research_group_id', $selectedSeedling->research_group_id)
                            ->exists();
                        if (!$exists) {
                            $fail('El macroproyecto seleccionado no es válido para el grupo de investigación de este semillero.');
                        }
                    },
                ],
            ]);
        }

        DB::transaction(function () use ($validated, $request) {
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
                'tipo_financiacion'     => $validated['tipo_financiacion'] ?? null,
            ]);

            // 2. Vincular proyecto al semillero seleccionado
            DB::table('project_seedlings')->insert([
                'project_id'  => $proyecto->id,
                'seedling_id' => $validated['seedling_id'],
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
        $proyecto  = $this->findProyectoDelSemillero($id);

        $autores   = ProjectAuthor::with('user.person')->where('project_id', $id)->where('activo', true)->get();
        $productos = $proyecto->products()->with('groupProducts.mincienciasTypology')->get();
        $evidencias = $proyecto->projectEvidences()->latest()->get();
        $macro     = $proyecto->macroProject;

        return view('asesor_semillero.proyectos.show', compact(
            'proyecto', 'autores', 'productos', 'evidencias', 'macro'
        ));
    }

    /**
     * Permiso: proyectos.editar
     * Muestra formulario de edición del proyecto.
     */
    public function edit(int $id): View
    {
        $semilleros     = $this->getSemillerosDelAsesor();
        $proyecto       = $this->findProyectoDelSemillero($id);
        $lineasInves    = ResearchLine::orderBy('nombre')->get();
        $lineasTec      = TechnologicalLine::orderBy('nombre')->get();
        $areasTematicas = ThematicArea::orderBy('nombre')->get();
        $modalidades    = ProjectModality::orderBy('nombre')->get();
        
        $group_ids      = $semilleros->pluck('research_group_id')->filter()->unique();
        $macroProyectos = MacroProject::whereIn('research_group_id', $group_ids)
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();
            
        $proyecto_semillero_id = DB::table('project_seedlings')->where('project_id', $id)->value('seedling_id');

        return view('asesor_semillero.proyectos.edit', compact(
            'proyecto', 'lineasInves', 'lineasTec', 'areasTematicas', 'modalidades', 'macroProyectos', 'semilleros', 'proyecto_semillero_id'
        ));
    }

    /**
     * Permiso: proyectos.editar
     * Actualiza el proyecto. El tipo de investigación NO puede modificarse.
     */
    public function update(StoreProyectoRequest $request, int $id): RedirectResponse
    {
        $proyecto  = $this->findProyectoDelSemillero($id);
        $validated = $request->validated();
        
        // El semillero_id viene validado, buscamos su research_group_id
        $semilleros = $this->getSemillerosDelAsesor();
        $selectedSeedling = $semilleros->firstWhere('id', $validated['seedling_id']);
        if (!$selectedSeedling) {
            return redirect()->back()->with('error', 'Semillero inválido.');
        }

        if ($validated['tiene_macroproyecto']) {
            $request->validate([
                'macro_project_id' => [
                    'required',
                    function ($attribute, $value, $fail) use ($selectedSeedling) {
                        $exists = MacroProject::where('id', $value)
                            ->where('research_group_id', $selectedSeedling->research_group_id)
                            ->exists();
                        if (!$exists) {
                            $fail('El macroproyecto seleccionado no es válido para el grupo de investigación de este semillero.');
                        }
                    },
                ],
            ]);
        }

        DB::transaction(function () use ($proyecto, $validated, $selectedSeedling, $request) {
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
                'tipo_financiacion'     => $validated['tipo_financiacion'] ?? null,
            ]);
            
            // Actualizar vinculación de semillero
            DB::table('project_seedlings')->where('project_id', $proyecto->id)->update([
                'seedling_id' => $validated['seedling_id'],
                'updated_at'  => now(),
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
        $proyecto  = $this->findProyectoDelSemillero($id);
        $semillero_id = DB::table('project_seedlings')->where('project_id', $proyecto->id)->value('seedling_id');
        $semillero = Seedling::find($semillero_id);

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
        $projectIds = DB::table('project_seedlings')->where('seedling_id', $semillero->id)->pluck('project_id');
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

        $proyecto  = $this->findProyectoDelSemillero($id);
        $semillero_id = DB::table('project_seedlings')->where('project_id', $proyecto->id)->value('seedling_id');
        $semillero = Seedling::find($semillero_id);

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
        $proyecto = $this->findProyectoDelSemillero($id);

        if ($user_id === Auth::id()) {
            return redirect()->back()->with('error', 'No puedes desvincularte a ti mismo del proyecto.');
        }

        ProjectAuthor::where('project_id', $id)
            ->where('user_id', $user_id)
            ->update(['activo' => false]);

        return redirect()->back()->with('success', 'Integrante desvinculado del proyecto.');
    }

    /**
     * Permiso: proyectos.editar
     * Desactiva/activa el proyecto (toggle de estado).
     */
    public function deactivate(int $id): RedirectResponse
    {
        $proyecto  = $this->findProyectoDelSemillero($id);

        $nuevoEstado = $proyecto->estado === EstadoEnum::Activo
            ? EstadoEnum::Inactivo
            : EstadoEnum::Activo;

        $proyecto->update(['estado' => $nuevoEstado]);

        $msg = $nuevoEstado === EstadoEnum::Activo ? 'Proyecto activado correctamente.' : 'Proyecto desactivado correctamente.';
        return redirect()->route('asesor.proyectos.index')->with('success', $msg);
    }

}
