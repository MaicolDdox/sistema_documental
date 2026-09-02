<?php

namespace App\Http\Controllers\LiderProyecto;

use App\Concerns\StreamsPublicStorageFiles;
use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Http\Controllers\Controller;
use App\Models\ProjectEvidence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvidenciaController extends Controller
{
    use LiderProyectoContext;
    use StreamsPublicStorageFiles;

    public function index(): View
    {
        $proyecto = $this->miProyecto();

        $desarrollo = $proyecto->projectEvidences()
            ->where('tipo', TipoEvidenciaEnum::Desarrollo)
            ->latest()->get();

        $formulacion = $proyecto->projectEvidences()
            ->where('tipo', TipoEvidenciaEnum::Formulacion)
            ->latest()->get();

        $ejecucion = $proyecto->projectEvidences()
            ->where('tipo', TipoEvidenciaEnum::Ejecucion)
            ->latest()->get();

        $productoFinal = $proyecto->projectEvidences()
            ->where('tipo', TipoEvidenciaEnum::ProductoFinal)
            ->latest()->get();

        return view('lider_proyecto.evidencias.index', compact('proyecto', 'desarrollo', 'formulacion', 'ejecucion', 'productoFinal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $proyecto = $this->miProyecto();

        $validated = $request->validate([
            'tipo' => ['required', Rule::enum(TipoEvidenciaEnum::class)],
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'archivo' => 'required|file|max:10240',
        ]);

        $path = $request->file('archivo')->store('evidencias-proyecto', 'public');

        $requiereRevision = in_array($validated['tipo'], [
            TipoEvidenciaEnum::Formulacion->value,
            TipoEvidenciaEnum::Ejecucion->value,
            TipoEvidenciaEnum::ProductoFinal->value,
        ], true);

        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => $validated['tipo'],
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'archivo' => $path,
            'uploaded_by' => Auth::id(),
            'estado_revision_lider' => $requiereRevision ? EstadoRevisionEnum::Pendiente : null,
            // La sube él mismo: no hay novedad que notificarle a sí mismo.
            'visto_por_lider_proyecto_at' => now(),
        ]);

        $mensajes = [
            TipoEvidenciaEnum::Formulacion->value => 'Evidencia de formulación enviada — quedó pendiente de revisión del Líder de Semillero.',
            TipoEvidenciaEnum::Ejecucion->value => 'Evidencia de ejecución enviada — quedó pendiente de revisión del Líder de Semillero.',
            TipoEvidenciaEnum::ProductoFinal->value => 'Evidencia de producto final enviada — quedó pendiente de revisión del Líder de Semillero.',
        ];
        $mensaje = $mensajes[$validated['tipo']] ?? 'Evidencia de investigación y/o desarrollo subida correctamente.';

        return redirect()->route('lider-proyecto.evidencias.index')->with('success', $mensaje);
    }

    public function destroy(ProjectEvidence $evidencia): RedirectResponse
    {
        $proyecto = $this->miProyecto();

        if ((int) $evidencia->project_id !== (int) $proyecto->id || (int) $evidencia->uploaded_by !== Auth::id()) {
            abort(403, 'Solo puedes eliminar evidencias que tú mismo subiste.');
        }

        if ($evidencia->archivo) {
            Storage::disk('public')->delete($evidencia->archivo);
        }
        $evidencia->delete();

        return redirect()->route('lider-proyecto.evidencias.index')->with('success', 'Evidencia eliminada correctamente.');
    }

    public function descargar(ProjectEvidence $evidencia): StreamedResponse
    {
        $this->ensureEvidenciaDelProyecto($evidencia);

        return $this->descargarArchivoPublico($evidencia->archivo, $evidencia->nombre);
    }

    private function ensureEvidenciaDelProyecto(ProjectEvidence $evidencia): void
    {
        $proyecto = $this->miProyecto();
        if ((int) $evidencia->project_id !== (int) $proyecto->id) {
            abort(403, 'Esta evidencia no pertenece a tu proyecto.');
        }
    }
}
