<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Http\Controllers\Controller;
use App\Models\SeedlingInternalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class DocInternaController extends Controller
{
    private const TIPOS = ['acta' => 'Acta', 'informe' => 'Informe', 'otro' => 'Otro'];

    public static function tipos(): array
    {
        return self::TIPOS;
    }

    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        $documentos = collect();

        if ($semillero) {
            $documentos = SeedlingInternalDocument::with('user.person')
                ->where('seedling_id', $semillero->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($doc) {
                    $doc->subido_por_mi = $doc->user_id === Auth::id();
                    $doc->subido_por_nombre = $doc->user?->person?->nombre_completo ?? $doc->user?->email ?? '—';
                    $doc->size_bytes = Storage::disk('public')->exists($doc->url_archivo)
                        ? Storage::disk('public')->size($doc->url_archivo)
                        : 0;
                    $doc->archivo_nombre = basename((string) $doc->url_archivo);
                    return $doc;
                });
        }

        return view('lider_semillero.doc_interna.index', [
            'semillero'   => $semillero,
            'documentos' => $documentos,
            'tipos'       => self::tipos(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            return redirect()->route('lider-sem.doc-interna')->with('error', 'No tienes un semillero asignado.');
        }

        $request->validate([
            'titulo'  => 'required|string|max:255',
            'tipo'    => 'required|in:acta,informe,otro',
            'archivo' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ], [
            'titulo.required'  => 'El título es obligatorio.',
            'archivo.required' => 'Debes seleccionar un archivo.',
        ]);

        $path = $request->file('archivo')->store('doc_interna/' . $semillero->id, 'public');

        SeedlingInternalDocument::create([
            'seedling_id'  => $semillero->id,
            'user_id'      => Auth::id(),
            'titulo'       => $request->titulo,
            'tipo'         => $request->tipo,
            'url_archivo'  => $path,
        ]);

        return redirect()->route('lider-sem.doc-interna')->with('success', 'Documento subido correctamente.');
    }

    public function ver(SeedlingInternalDocument $documento): StreamedResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (! $semillero || $documento->seedling_id !== $semillero->id) {
            abort(403, 'No puedes ver este documento.');
        }
        if (!Storage::disk('public')->exists($documento->url_archivo)) {
            abort(404, 'El archivo no existe.');
        }

        return Storage::disk('public')->response(
            $documento->url_archivo,
            basename((string) $documento->url_archivo)
        );
    }

    public function descargar(SeedlingInternalDocument $documento): StreamedResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (! $semillero || $documento->seedling_id !== $semillero->id) {
            abort(403, 'No puedes descargar este documento.');
        }
        if (!Storage::disk('public')->exists($documento->url_archivo)) {
            abort(404, 'El archivo no existe.');
        }

        return Storage::disk('public')->download(
            $documento->url_archivo,
            basename((string) $documento->url_archivo)
        );
    }

    public function destroy(SeedlingInternalDocument $documento): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero || $documento->seedling_id !== $semillero->id) {
            abort(403, 'No puedes eliminar este documento.');
        }
        if ($documento->user_id !== Auth::id()) {
            return redirect()->route('lider-sem.doc-interna')->with('error', 'Solo puedes eliminar los documentos que tú subiste.');
        }

        if (Storage::disk('public')->exists($documento->url_archivo)) {
            Storage::disk('public')->delete($documento->url_archivo);
        }
        $documento->delete();

        return redirect()->route('lider-sem.doc-interna')->with('success', 'Documento eliminado.');
    }
}
