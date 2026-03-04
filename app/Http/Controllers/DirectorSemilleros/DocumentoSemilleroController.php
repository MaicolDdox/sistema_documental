<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\SeedlingFile;
use App\Models\Seedling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentoSemilleroController extends Controller
{
    public function index()
    {
        $this->authorize('documentos.listar');

        $user = Auth::user();

        // lista de documentos del centro con filtros
        $documentos = SeedlingFile::with(['seedling', 'user.person'])
            ->whereHas('seedling.leader', function($q) use ($user) {
                $q->where('training_center_id', $user->training_center_id);
            })
            ->paginate(10);

        return view('director_semilleros.documentos.index', compact('documentos'));
    }

    public function create()
    {
        $this->authorize('documentos.subir');

        $user = Auth::user();
        $semilleros = Seedling::whereHas('leader', function($q) use ($user) {
            $q->where('training_center_id', $user->training_center_id);
        })->get();

        return view('director_semilleros.documentos.create', compact('semilleros'));
    }

    public function store(Request $request)
    {
        $this->authorize('documentos.subir');

        $validated = $request->validate([
            'archivo'      => 'required|file|mimes:pdf,docx,xlsx|max:10240',
            'nombre'       => 'required|string|max:200',
            'semillero_id' => 'nullable|exists:seedlings,id',
        ]);

        $path = $request->file('archivo')->store('documentos', 'public');

        SeedlingFile::create([
            'seedling_id' => $validated['semillero_id'] ?? null,
            'user_id'     => Auth::id(),
            'archivo'     => $validated['nombre'],
            'url_archivo' => $path,
        ]);

        return redirect()->route('dir-sem.documentos.index')
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
}
