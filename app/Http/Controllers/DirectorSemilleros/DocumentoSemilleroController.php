<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\Seedling;
use App\Models\SeedlingFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Documentos por semillero. Se gestionan desde el tab "Documentos" dentro
 * del detalle de cada semillero (director_semilleros.semilleros.show) — no
 * hay listado/formulario propios, siempre van ligados a un semillero.
 */
class DocumentoSemilleroController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('documentos.subir');

        $validated = $request->validate([
            'archivo' => 'required|file|mimes:pdf,docx,xlsx|max:10240',
            'nombre' => 'required|string|max:200',
            'semillero_id' => 'required|exists:seedlings,id',
        ]);

        // BUG-20260813-032 — sin esto, un director podía subir un documento
        // a un semillero de OTRO centro de formación con solo conocer su id.
        $semillero = Seedling::findOrFail($validated['semillero_id']);
        $this->ensureDelCentro($semillero);

        $path = $request->file('archivo')->store('documentos', 'public');
        if ($path === false) {
            return redirect()->back()->with('error', 'No se pudo guardar el archivo. Verifica los permisos de almacenamiento.');
        }

        SeedlingFile::create([
            'seedling_id' => $validated['semillero_id'],
            'user_id' => Auth::id(),
            'archivo' => $validated['nombre'],
            'url_archivo' => $path,
        ]);

        return redirect()->route('dir-sem.semilleros.show', $validated['semillero_id'])
            ->with('success', 'Documento subido correctamente.');
    }

    public function destroy($id)
    {
        $this->authorize('documentos.eliminar_propio');

        $documento = SeedlingFile::findOrFail($id);

        if ($documento->user_id !== Auth::id()) {
            abort(403, 'No puedes eliminar un documento que no es tuyo.');
        }

        if (Storage::disk('public')->exists($documento->url_archivo)) {
            Storage::disk('public')->delete($documento->url_archivo);
        }

        $documento->delete();

        return redirect()->back()->with('success', 'Documento eliminado correctamente.');
    }

    /**
     * Mismo criterio que DirectorSemilleros\SemilleroController::checkCentroFormacion:
     * scope principal seedlings.training_center_id, fallback al centro de
     * quien creó el semillero (semilleros legacy sin training_center_id propio).
     */
    private function ensureDelCentro(Seedling $semillero): void
    {
        $centerId = Auth::user()->training_center_id;
        $centerOfRecord = $semillero->training_center_id ?? $semillero->creator?->training_center_id;

        if ($centerOfRecord !== null && (int) $centerOfRecord !== (int) $centerId) {
            abort(403, 'No puedes subir documentos a un semillero de otro centro de formación.');
        }
    }
}
