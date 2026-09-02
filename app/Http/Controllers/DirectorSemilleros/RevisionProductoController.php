<?php

namespace App\Http\Controllers\DirectorSemilleros;

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
 * Segunda y última etapa de aprobación del producto final (rediseño de
 * roles) — solo evidencias ya aprobadas por el Líder de Semillero.
 */
class RevisionProductoController extends Controller
{
    use StreamsPublicStorageFiles;

    public function __construct(private readonly RevisionEvidenciaService $revision) {}

    public function index(): View
    {
        $centerId = Auth::user()->training_center_id;

        $evidencias = ProjectEvidence::with(['project.liderProyecto.person', 'project.seedling', 'uploadedBy.person'])
            ->where('tipo', TipoEvidenciaEnum::ProductoFinal)
            ->where('estado_revision_lider', EstadoRevisionEnum::Aprobado)
            ->whereHas('project.seedling', fn ($q) => $q->where('training_center_id', $centerId))
            ->orderByRaw("CASE WHEN estado_revision_director = 'pendiente' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        return view('director_semilleros.productos.index', compact('evidencias'));
    }

    public function aprobar(Request $request, ProjectEvidence $evidencia): RedirectResponse
    {
        $this->authorize('productos.aprobar_final');
        $this->ensureEvidenciaDelCentro($evidencia);

        $this->revision->aprobarEtapaDirector($evidencia, Auth::user(), $request->input('observaciones'));

        return redirect()->route('dir-sem.productos.index')->with('success', 'Producto aprobado definitivamente.');
    }

    public function rechazar(Request $request, ProjectEvidence $evidencia): RedirectResponse
    {
        $this->authorize('productos.rechazar_final');
        $validated = $request->validate([
            'observaciones' => 'required|string|max:2000',
        ], [
            'observaciones.required' => 'Las observaciones son obligatorias al rechazar.',
        ]);
        $this->ensureEvidenciaDelCentro($evidencia);

        $this->revision->rechazarEtapaDirector($evidencia, Auth::user(), $validated['observaciones']);

        return redirect()->route('dir-sem.productos.index')
            ->with('success', 'Producto rechazado. El Líder de Proyecto deberá corregirlo y volver a subirlo.');
    }

    public function descargar(ProjectEvidence $evidencia): StreamedResponse
    {
        $this->ensureEvidenciaCentroSolamente($evidencia);

        return $this->descargarArchivoPublico($evidencia->archivo, $evidencia->nombre);
    }

    private function ensureEvidenciaDelCentro(ProjectEvidence $evidencia): void
    {
        $this->ensureEvidenciaCentroSolamente($evidencia);
        if ($evidencia->estado_revision_lider !== EstadoRevisionEnum::Aprobado) {
            abort(403, 'Este producto todavía no fue aprobado por el Líder de Semillero.');
        }
    }

    private function ensureEvidenciaCentroSolamente(ProjectEvidence $evidencia): void
    {
        $centerId = Auth::user()->training_center_id;
        if ((int) $evidencia->project?->seedling?->training_center_id !== (int) $centerId) {
            abort(403, 'Este producto no pertenece a tu centro de formación.');
        }
    }
}
