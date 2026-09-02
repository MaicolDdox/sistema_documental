<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Concerns\StreamsPublicStorageFiles;
use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Http\Controllers\Controller;
use App\Models\ProjectEvidence;
use App\Services\LiderSemillero\RevisionEvidenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Primera etapa de aprobación del producto final (rediseño de roles).
 * El "producto" es la evidencia tipo=producto_final de un proyecto del
 * semillero — ya no existe Product/GroupProduct como registro separado.
 */
class ProductosController extends Controller
{
    use StreamsPublicStorageFiles;

    public function __construct(private readonly RevisionEvidenciaService $revision) {}

    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        $proyectosConEvidencias = collect();

        $tiposRevisables = [
            TipoEvidenciaEnum::Formulacion,
            TipoEvidenciaEnum::Ejecucion,
            TipoEvidenciaEnum::ProductoFinal,
        ];

        if ($semillero) {
            $evidencias = ProjectEvidence::with(['project.liderProyecto.person', 'uploadedBy.person'])
                ->whereIn('tipo', $tiposRevisables)
                ->whereHas('project', fn ($q) => $q->where('seedling_id', $semillero->id))
                ->orderByRaw("CASE WHEN estado_revision_lider = 'pendiente' THEN 0 ELSE 1 END")
                ->latest()
                ->get();

            // Punto rojo del sidebar: al visitar este listado, lo que el
            // director haya resuelto sobre productos ya aprobados por este
            // líder queda "visto".
            ProjectEvidence::whereIn('tipo', $tiposRevisables)
                ->whereHas('project', fn ($q) => $q->where('seedling_id', $semillero->id))
                ->whereNull('visto_por_lider_semillero_at')
                ->update(['visto_por_lider_semillero_at' => now()]);

            // Agrupadas por proyecto: solo aparecen los proyectos que
            // tienen al menos una evidencia revisable, con los que tienen
            // pendientes primero.
            $proyectosConEvidencias = $evidencias->groupBy('project_id')
                ->map(function ($grupo) {
                    return (object) [
                        'proyecto' => $grupo->first()->project,
                        'evidencias' => $grupo,
                        'pendientes_count' => $grupo->filter(
                            fn ($ev) => $ev->estado_revision_lider === EstadoRevisionEnum::Pendiente
                        )->count(),
                    ];
                })
                ->sortByDesc(fn ($grupo) => $grupo->pendientes_count)
                ->values();
        }

        return view('lider_semillero.productos.index', compact('semillero', 'proyectosConEvidencias'));
    }

    public function aprobar(Request $request, ProjectEvidence $evidencia): RedirectResponse
    {
        $this->authorize('productos.aprobar');
        $this->ensureEvidenciaDelSemillero($evidencia);

        if ($evidencia->tipo === TipoEvidenciaEnum::ProductoFinal) {
            $this->revision->aprobarEtapaLider($evidencia, Auth::user(), $request->input('observaciones'));

            return redirect()->route('lider-sem.productos')
                ->with('success', 'Producto final aprobado — pasa a revisión del Director de Semilleros.');
        }

        $this->revision->aprobarEtapaUnica($evidencia, Auth::user(), $request->input('observaciones'));

        $porcentaje = $evidencia->tipo === TipoEvidenciaEnum::Formulacion ? '30%' : '50%';

        return redirect()->route('lider-sem.productos')
            ->with('success', "Evidencia aprobada — cuenta {$porcentaje} del avance del proyecto.");
    }

    public function rechazar(Request $request, ProjectEvidence $evidencia): RedirectResponse
    {
        $this->authorize('productos.rechazar');
        $validated = $request->validate([
            'observaciones' => 'required|string|max:2000',
        ], [
            'observaciones.required' => 'Las observaciones son obligatorias al rechazar.',
        ]);
        $this->ensureEvidenciaDelSemillero($evidencia);

        if ($evidencia->tipo === TipoEvidenciaEnum::ProductoFinal) {
            $this->revision->rechazarEtapaLider($evidencia, Auth::user(), $validated['observaciones']);

            return redirect()->route('lider-sem.productos')
                ->with('success', 'Producto final rechazado. El Líder de Proyecto deberá corregirlo y volver a subirlo.');
        }

        $this->revision->rechazarEtapaUnica($evidencia, Auth::user(), $validated['observaciones']);

        return redirect()->route('lider-sem.productos')
            ->with('success', 'Evidencia rechazada. El Líder de Proyecto deberá corregirla y volver a subirla.');
    }

    public function descargar(ProjectEvidence $evidencia): StreamedResponse
    {
        $this->ensureEvidenciaDelSemillero($evidencia);

        return $this->descargarArchivoPublico($evidencia->archivo, $evidencia->nombre);
    }

    private function ensureEvidenciaDelSemillero(ProjectEvidence $evidencia): void
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (! $semillero || (int) $evidencia->project?->seedling_id !== (int) $semillero->id) {
            abort(403, 'Esta evidencia no pertenece a tu semillero.');
        }
        if (! in_array($evidencia->tipo, [
            TipoEvidenciaEnum::Formulacion,
            TipoEvidenciaEnum::Ejecucion,
            TipoEvidenciaEnum::ProductoFinal,
        ], true)) {
            abort(403, 'Solo se pueden aprobar o rechazar evidencias de formulación, ejecución o producto final.');
        }
    }
}
