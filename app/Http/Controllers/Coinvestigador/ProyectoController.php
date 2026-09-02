<?php

namespace App\Http\Controllers\Coinvestigador;

use App\Concerns\StreamsPublicStorageFiles;
use App\Enums\TipoEvidenciaEnum;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectEvidence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Un co_investigador puede estar vinculado a varios proyectos, por eso
 * (a diferencia de LiderProyectoContext::miProyecto()) cada acción recibe
 * el {proyecto} explícito y valida la vinculación activa contra
 * project_authors antes de continuar.
 */
class ProyectoController extends Controller
{
    use StreamsPublicStorageFiles;

    public function show(Project $proyecto): View
    {
        $this->ensureVinculado($proyecto);

        $proyecto->load([
            'liderProyecto.person',
            'learners',
            'evidenciasDesarrollo.uploadedBy.person',
            'evidenciasProductoFinal' => fn ($q) => $q->latest(),
        ]);

        return view('co_investigador.proyectos.show', compact('proyecto'));
    }

    public function storeEvidencia(Request $request, Project $proyecto): RedirectResponse
    {
        $this->ensureVinculado($proyecto);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'archivo' => 'required|file|max:10240',
        ]);

        $path = $request->file('archivo')->store('evidencias-proyecto', 'public');

        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::Desarrollo,
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'archivo' => $path,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('co-investigador.proyectos.show', $proyecto)
            ->with('success', 'Evidencia subida correctamente.');
    }

    public function destroyEvidencia(ProjectEvidence $evidencia): RedirectResponse
    {
        $this->ensureVinculado($evidencia->project);

        if ((int) $evidencia->uploaded_by !== Auth::id()) {
            abort(403, 'Solo puedes eliminar evidencias que tú mismo subiste.');
        }

        if ($evidencia->archivo) {
            Storage::disk('public')->delete($evidencia->archivo);
        }
        $evidencia->delete();

        return redirect()->route('co-investigador.proyectos.show', $evidencia->project_id)
            ->with('success', 'Evidencia eliminada correctamente.');
    }

    public function descargarEvidencia(ProjectEvidence $evidencia): StreamedResponse
    {
        $this->ensureVinculado($evidencia->project);

        return $this->descargarArchivoPublico($evidencia->archivo, $evidencia->nombre);
    }

    private function ensureVinculado(Project $proyecto): void
    {
        $vinculado = $proyecto->authors()
            ->wherePivot('activo', true)
            ->where('users.id', Auth::id())
            ->exists();

        if (! $vinculado) {
            abort(403, 'No estás vinculado a este proyecto.');
        }
    }
}
