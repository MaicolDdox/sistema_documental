<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Http\Controllers\Controller;
use App\Models\SeedlingFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ArchivosSemilleroController extends Controller
{
    private const MAX_SIZE_MB = 20;
    private const ALLOWED_MIMES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];

    /**
     * Lista archivos del semillero que lidera el usuario (tabla: seedling_files).
     */
    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        $archivos = collect();

        if ($semillero) {
            $archivos = SeedlingFile::with('user.person')
                ->where('seedling_id', $semillero->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($file) {
                    $file->size_bytes = Storage::disk('public')->exists($file->url_archivo)
                        ? Storage::disk('public')->size($file->url_archivo)
                        : 0;
                    $file->subido_por_mi = $file->user_id === Auth::id();
                    if ($file->subido_por_mi) {
                        $file->subido_por_nombre = 'ti';
                    } else {
                        $uploader = $file->user;
                        $file->subido_por_nombre = $uploader && $uploader->hasRole('director_semilleros')
                            ? 'Director de Semilleros'
                            : ($uploader?->person?->nombre_completo ?? $uploader?->email ?? 'Otro');
                    }
                    return $file;
                });
        }

        return view('lider_semillero.archivos.index', [
            'semillero' => $semillero,
            'archivos'  => $archivos,
        ]);
    }

    /**
     * Descarga el archivo. La opción de "ver en el navegador" (inline) se
     * eliminó del sistema completo (BUG-20260813-030): solo queda descarga.
     */
    public function descargar(SeedlingFile $archivo): StreamedResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (! $semillero || $archivo->seedling_id !== $semillero->id) {
            abort(403, 'No puedes descargar este archivo.');
        }

        if (!Storage::disk('public')->exists($archivo->url_archivo)) {
            abort(404, 'El archivo no existe.');
        }

        return Storage::disk('public')->download(
            $archivo->url_archivo,
            $archivo->archivo ?? basename($archivo->url_archivo)
        );
    }

    /**
     * Sube un archivo al semillero del líder.
     */
    public function store(Request $request): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            return redirect()->route('lider-sem.archivos')->with('error', 'No tienes un semillero asignado.');
        }

        $maxKb = self::MAX_SIZE_MB * 1024;
        $request->validate([
            'archivo' => 'required|file|mimes:' . implode(',', self::ALLOWED_MIMES) . '|max:' . $maxKb,
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.mimes'    => 'Formatos permitidos: PDF, DOCX, XLSX, PPTX, JPG, PNG.',
            'archivo.max'      => 'Tamaño máximo: ' . self::MAX_SIZE_MB . ' MB.',
        ]);

        $uploaded = $request->file('archivo');
        $path = $uploaded->store('archivos_semillero/' . $semillero->id, 'public');
        if ($path === false) {
            return redirect()->back()->with('error', 'No se pudo guardar el archivo. Verifica los permisos de almacenamiento.');
        }
        $nombreOriginal = $uploaded->getClientOriginalName();

        SeedlingFile::create([
            'seedling_id' => $semillero->id,
            'user_id'     => Auth::id(),
            'archivo'     => $nombreOriginal,
            'url_archivo' => $path,
        ]);

        return redirect()->route('lider-sem.archivos')->with('success', 'Archivo subido correctamente.');
    }

    /**
     * Elimina un archivo. Solo si lo subió el usuario actual.
     */
    public function destroy(SeedlingFile $archivo): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero || $archivo->seedling_id !== $semillero->id) {
            abort(403, 'No puedes eliminar este archivo.');
        }
        if ($archivo->user_id !== Auth::id()) {
            return redirect()->route('lider-sem.archivos')
                ->with('error', 'Solo puedes eliminar los archivos que tú subiste.');
        }

        if (Storage::disk('public')->exists($archivo->url_archivo)) {
            Storage::disk('public')->delete($archivo->url_archivo);
        }
        $archivo->delete();

        return redirect()->route('lider-sem.archivos')->with('success', 'Archivo eliminado.');
    }
}
