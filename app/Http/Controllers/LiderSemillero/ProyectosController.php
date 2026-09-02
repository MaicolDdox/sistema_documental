<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Concerns\StreamsPublicStorageFiles;
use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Enums\TipoProyectoOrigenEnum;
use App\Http\Controllers\Controller;
use App\Models\InvestigationType;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use App\Models\User;
use App\Services\LiderSemillero\ProyectoLiderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProyectosController extends Controller
{
    use StreamsPublicStorageFiles;
    public function __construct(
        private readonly ProyectoLiderService $proyectoLider,
    ) {}

    /**
     * Lista los proyectos del semillero que lidera el usuario.
     */
    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();

        $proyectos = collect();
        if ($semillero) {
            $proyectos = Project::with(['liderProyecto.person'])
                ->withCount(['projectAuthors as integrantes_count', 'learners as aprendices_count'])
                ->where('seedling_id', $semillero->id)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($project) {
                    $project->avance = $this->calcularAvance($project);
                    $project->avance_label = $this->etiquetaAvance($project->avance);

                    return $project;
                });
        }

        return view('lider_semillero.proyectos.index', [
            'semillero' => $semillero,
            'proyectos' => $proyectos,
        ]);
    }

    public function create(): View
    {
        $this->authorize('proyectos.crear');

        $semillero = Auth::user()->ledSeedlings()->firstOrFail();

        return view('lider_semillero.proyectos.create', array_merge(
            ['semillero' => $semillero],
            $this->datosFormulario()
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('proyectos.crear');

        $semillero = Auth::user()->ledSeedlings()->firstOrFail();
        $validated = $this->validarProyecto($request, $semillero);

        $this->proyectoLider->crearProyecto($validated, $semillero);

        return redirect()->route('lider-sem.proyectos')
            ->with('success', 'Proyecto creado y asignado al líder de proyecto seleccionado.');
    }

    public function show(Project $proyecto): View
    {
        $this->authorize('proyectos.ver_detalle');
        $semillero = $this->ensureProyectoDelSemillero($proyecto);

        $proyecto->load([
            'liderProyecto.person',
            'learners',
            'authors' => fn ($q) => $q->wherePivot('activo', true),
            'evidenciasDesarrollo',
            'researchLine',
            'technologicalLine',
            'thematicArea',
            'projectModality',
            'investigationType',
        ]);

        return view('lider_semillero.proyectos.show', compact('semillero', 'proyecto'));
    }

    public function edit(Project $proyecto): View
    {
        $this->authorize('proyectos.editar');
        $this->ensureProyectoDelSemillero($proyecto);

        return view('lider_semillero.proyectos.edit', array_merge(
            ['proyecto' => $proyecto],
            $this->datosFormulario()
        ));
    }

    public function update(Request $request, Project $proyecto): RedirectResponse
    {
        $this->authorize('proyectos.editar');
        $semillero = $this->ensureProyectoDelSemillero($proyecto);

        $validated = $this->validarProyecto($request, $semillero, $proyecto);

        $this->proyectoLider->actualizarProyecto($proyecto, $validated);

        return redirect()->route('lider-sem.proyectos')
            ->with('success', 'Proyecto actualizado correctamente.');
    }

    /**
     * Calcula avance real del proyecto (0-100) según las 3 fases de evidencia
     * aprobadas: Formulación 30%, Ejecución 50%, Producto Final 20% (requiere
     * la doble aprobación completa lider+director, no solo la del líder).
     */
    private function calcularAvance(Project $project): int
    {
        $avance = 0;

        if ($this->etapaLiderAprobada($project, TipoEvidenciaEnum::Formulacion)) {
            $avance += 30;
        }
        if ($this->etapaLiderAprobada($project, TipoEvidenciaEnum::Ejecucion)) {
            $avance += 50;
        }
        if ($project->projectEvidences()
            ->where('tipo', TipoEvidenciaEnum::ProductoFinal)
            ->where('estado_revision_director', EstadoRevisionEnum::Aprobado)
            ->exists()) {
            $avance += 20;
        }

        return $avance;
    }

    private function etapaLiderAprobada(Project $project, TipoEvidenciaEnum $tipo): bool
    {
        return $project->projectEvidences()
            ->where('tipo', $tipo)
            ->where('estado_revision_lider', EstadoRevisionEnum::Aprobado)
            ->exists();
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

    public function descargarEvidencia(ProjectEvidence $evidencia): StreamedResponse
    {
        $this->ensureEvidenciaDelSemillero($evidencia);

        return $this->descargarArchivoPublico($evidencia->archivo, $evidencia->nombre);
    }

    private function ensureEvidenciaDelSemillero(ProjectEvidence $evidencia): void
    {
        $semillero = Auth::user()->ledSeedlings()->firstOrFail();
        if ((int) $evidencia->project?->seedling_id !== (int) $semillero->id) {
            abort(403, 'Esta evidencia no pertenece a tu semillero.');
        }
    }

    private function ensureProyectoDelSemillero(Project $proyecto): \App\Models\Seedling
    {
        $semillero = Auth::user()->ledSeedlings()->firstOrFail();
        if ((int) $proyecto->seedling_id !== (int) $semillero->id) {
            abort(403, 'Este proyecto no pertenece a tu semillero.');
        }

        return $semillero;
    }

    private function validarProyecto(Request $request, \App\Models\Seedling $semillero, ?Project $proyecto = null): array
    {
        // Líderes de Proyecto creados por este mismo Líder de Semillero, o
        // de su mismo centro de formación (BUG-20260813-058 — ver
        // datosFormulario() para el detalle de por qué se amplió).
        $liderProyectoIds = User::role('lider_proyecto')
            ->where(function ($q) {
                $q->where('created_by_user_id', Auth::id())
                    ->orWhere('training_center_id', Auth::user()->training_center_id);
            })
            ->pluck('id')
            ->all();

        return $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'lider_proyecto_user_id' => [
                'required',
                Rule::in($liderProyectoIds),
                Rule::unique('projects', 'lider_proyecto_user_id')->ignore($proyecto?->id),
            ],
            'research_line_id' => ['required', 'exists:research_lines,id'],
            'technological_line_id' => ['nullable', 'exists:technological_lines,id'],
            'thematic_area_id' => ['nullable', 'exists:thematic_areas,id'],
            'project_modality_id' => ['nullable', 'exists:project_modalities,id'],
            'investigation_type_id' => ['nullable', 'exists:investigation_types,id'],
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'tipo_financiacion' => ['nullable', Rule::in(['capacidad_instalada', 'financiado', 'con_alianza'])],
            'tipo_proyecto_origen' => ['nullable', Rule::enum(TipoProyectoOrigenEnum::class)],
        ], [
            'lider_proyecto_user_id.unique' => 'Este líder de proyecto ya está a cargo de otro proyecto. Cada líder de proyecto puede liderar solo uno.',
        ]);
    }

    private function datosFormulario(): array
    {
        return [
            // BUG-20260813-058: además de los que el propio líder de
            // semillero creó, también se ofrecen los lider_proyecto de su
            // mismo centro — un usuario con lider_proyecto como rol
            // secundario (multi-rol) nunca es "creado por" ningún líder de
            // semillero, así que sin esto quedaba sin forma posible de
            // recibir un proyecto asignado. No se quita el filtro por
            // creador, solo se amplía (nada de lo que ya funcionaba cambia).
            'lideresProyecto' => User::role('lider_proyecto')
                ->with('person')
                ->where(function ($q) {
                    $q->where('created_by_user_id', Auth::id())
                        ->orWhere('training_center_id', Auth::user()->training_center_id);
                })
                ->orderBy('email')
                ->get(),
            'lineasInvestigacion' => ResearchLine::orderBy('nombre')->get(),
            'lineasTecnologicas' => TechnologicalLine::orderBy('nombre')->get(),
            'areasTematicas' => ThematicArea::orderBy('nombre')->get(),
            'modalidades' => ProjectModality::orderBy('nombre')->get(),
            'tiposInvestigacion' => InvestigationType::orderBy('nombre')->get(),
            'tiposOrigen' => TipoProyectoOrigenEnum::cases(),
        ];
    }
}
