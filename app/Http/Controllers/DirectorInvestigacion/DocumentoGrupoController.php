<?php

namespace App\Http\Controllers\DirectorInvestigacion;

use App\Http\Controllers\Controller;
use App\Models\SeedlingInternalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Gestión de documentos institucionales del grupo de investigación.
 *
 * Reutiliza la tabla `seedling_internal_documents` identificando
 * los documentos del grupo mediante subject_type / subject_id
 * (o un campo grupo equivalente), sin crear una tabla nueva.
 *
 * TODO: si el sistema crece, crear una tabla `group_documents` propia.
 */
class DocumentoGrupoController extends Controller
{
    use DirectorContext;

    /**
     * Lista los documentos del grupo del director.
     */
    public function index(): View
    {
        $grupoId = $this->getGrupoId();

        $documentos = SeedlingInternalDocument::where('seedling_id', $grupoId)
            ->latest()
            ->paginate(20);

        return view('director_investigacion.documentos.index', compact('documentos', 'grupoId'));
    }

    /**
     * Almacena un nuevo documento institucional del grupo.
     */
    public function store(Request $request): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        $validated = $request->validate([
            'titulo'   => ['required', 'string', 'max:255'],
            'tipo'     => ['nullable', 'string', 'max:50'],
            'archivo'  => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:20480'],
        ]);

        $path = $request->file('archivo')->store("grupos/{$grupoId}/documentos", 'public');
        if ($path === false) {
            return redirect()->back()->with('error', 'No se pudo guardar el archivo. Verifica los permisos de almacenamiento.');
        }

        SeedlingInternalDocument::create([
            'seedling_id' => $grupoId,
            'user_id'     => Auth::id(),
            'titulo'      => $validated['titulo'],
            'tipo'        => $validated['tipo'] ?? 'otro',
            'url_archivo' => $path,
        ]);

        return redirect()
            ->route('director.documentos.index')
            ->with('success', 'Documento subido exitosamente.');
    }

    /**
     * Elimina un documento. Solo puede eliminarlo quien lo subió.
     */
    public function destroy(SeedlingInternalDocument $documento): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        abort_unless($documento->seedling_id === $grupoId, 403);
        abort_unless($documento->user_id === Auth::id(), 403, 'Solo puedes eliminar documentos que tú subiste.');

        if (Storage::disk('public')->exists($documento->url_archivo)) {
            Storage::disk('public')->delete($documento->url_archivo);
        }
        $documento->delete();

        return back()->with('success', 'Documento eliminado.');
    }

    /**
     * Descarga un documento institucional del grupo.
     * Solo el director autenticado cuyo grupo sea el dueño del documento puede descargarlo.
     */
    public function download(SeedlingInternalDocument $documento): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        abort_unless($documento->seedling_id === $grupoId, 403, 'No tienes permiso para descargar este documento.');

        if (! Storage::disk('public')->exists($documento->url_archivo)) {
            return back()->withErrors(['error' => 'El archivo no se encontró en el servidor.']);
        }

        $extension = pathinfo($documento->url_archivo, PATHINFO_EXTENSION);
        $nombreDescarga = $documento->titulo.($extension ? ".{$extension}" : '');

        return Storage::disk('public')->download($documento->url_archivo, $nombreDescarga);
    }
}
