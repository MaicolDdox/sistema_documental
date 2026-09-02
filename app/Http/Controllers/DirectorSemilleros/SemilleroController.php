<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Concerns\StreamsPublicStorageFiles;
use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\ProjectEvidence;
use App\Models\Seedling;
use App\Models\SeedlingFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SemilleroController extends Controller
{
    use StreamsPublicStorageFiles;
    /**
     * Listar semilleros con filtros (estado, búsqueda).
     */
    public function index(Request $request)
    {
        // permiso semilleros.listar (el middleware de rutas o el controller puede validar)
        // La instrucción dice: Proteger cada ruta con middleware('can:...') donde corresponda o @can.
        // Usaremos authorize en cada método para estar seguros.
        $this->authorize('semilleros.listar');

        $user = Auth::user();

        $query = Seedling::with(['leader.person', 'members', 'creator'])
            ->when(
                $user->training_center_id,
                fn ($q) => $q->where('training_center_id', $user->training_center_id)
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        $tab = $request->get('tab', 'todos');
        if ($tab === 'activos') {
            $query->where('estado', EstadoEnum::Activo);
        } elseif ($tab === 'inactivos') {
            $query->where('estado', EstadoEnum::Inactivo);
        }

        $semilleros = $query->orderBy('nombre')->paginate(10)->withQueryString();

        $lideres = User::role('lider_semillero')
            ->with('person')
            ->where('training_center_id', $user->training_center_id)
            ->whereDoesntHave('ledSeedlings')
            ->active()
            ->orderBy('email')
            ->get();

        $siguienteCodigo = $this->siguienteCodigoSugerido();

        return view('director_semilleros.semilleros.index', compact('semilleros', 'tab', 'lideres', 'siguienteCodigo'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        $this->authorize('semilleros.crear');

        $user = Auth::user();

        $lideres = User::role('lider_semillero')
            ->with('person')
            ->where('training_center_id', $user->training_center_id)
            ->whereDoesntHave('ledSeedlings')
            ->active()
            ->orderBy('email')
            ->get();

        $siguienteCodigo = $this->siguienteCodigoSugerido();

        return view('director_semilleros.semilleros.create', compact('lideres', 'siguienteCodigo'));
    }

    /**
     * Validar + crear semillero
     */
    public function store(Request $request)
    {
        $this->authorize('semilleros.crear');

        $request->merge([
            'codigo' => $request->input('codigo') ?: null,
        ]);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9]+$/'],
            'descripcion' => 'nullable|string',
            'lider_id' => 'nullable|exists:users,id',
        ], [
            'codigo.regex' => 'El código solo puede tener letras y números, sin espacios ni símbolos.',
        ]);

        if (! empty($validated['lider_id'])) {
            $lider = User::findOrFail($validated['lider_id']);
            if (! $lider->hasRole('lider_semillero')) {
                return back()->withErrors(['lider_id' => 'El usuario seleccionado no es líder de semillero.'])->withInput();
            }
            if ((int) $lider->training_center_id !== (int) Auth::user()->training_center_id) {
                return back()->withErrors(['lider_id' => 'El líder debe pertenecer a tu centro de formación.'])->withInput();
            }
            if (Seedling::where('leader_id', $lider->id)->exists()) {
                return back()->withErrors(['lider_id' => 'Este líder ya está vinculado a otro semillero.'])->withInput();
            }
        }

        $codigo = ! empty($validated['codigo']) ? $validated['codigo'] : $this->siguienteCodigoSugerido();

        Seedling::create([
            'creator_id' => Auth::id(),
            'leader_id' => $validated['lider_id'] ?? null,
            'training_center_id' => Auth::user()->training_center_id,
            'nombre' => $validated['nombre'],
            'codigo' => $codigo,
            'logo' => '',
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => EstadoEnum::Activo,
        ]);

        return redirect()->route('dir-sem.semilleros.index')
            ->with('success', 'Semillero creado correctamente.');
    }

    /**
     * Vista de detalle con tabs de supervisión
     */
    public function show(Seedling $semillero)
    {
        $this->authorize('semilleros.ver_detalle');

        // Validar que el semillero es de su centro
        $this->checkCentroFormacion($semillero);

        $semillero->loadCount(['members as integrantes_count', 'projects as proyectos_count']);
        $semillero->load([
            'projects.liderProyecto.person',
            'projects.learners',
            'projects.authors' => fn ($q) => $q->wherePivot('activo', true),
            'projects.evidenciasDesarrollo',
            'projects.evidenciasProductoFinal',
            'members.person',
        ]);

        $documentos = SeedlingFile::where('seedling_id', $semillero->id)
            ->with('user.person')
            ->latest()
            ->get();

        return view('director_semilleros.semilleros.show', compact('semillero', 'documentos'));
    }

    /**
     * Formulario de edición
     */
    public function edit(Seedling $semillero)
    {
        $this->authorize('semilleros.editar');
        $this->checkCentroFormacion($semillero);

        $user = Auth::user();

        $lideres = User::role('lider_semillero')
            ->where('training_center_id', $user->training_center_id)
            ->where(function ($q) use ($semillero) {
                $q->whereDoesntHave('ledSeedlings')
                    ->orWhere('id', $semillero->leader_id);
            })
            ->active()
            ->get();

        return view('director_semilleros.semilleros.edit', compact('semillero', 'lideres'));
    }

    /**
     * Validar + actualizar
     */
    public function update(Request $request, Seedling $semillero)
    {
        $this->authorize('semilleros.editar');
        $this->checkCentroFormacion($semillero);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'lider_id' => 'nullable|exists:users,id',
        ]);

        if (! empty($validated['lider_id'])) {
            $lider = User::findOrFail($validated['lider_id']);
            if (! $lider->hasRole('lider_semillero')) {
                return back()->withErrors(['lider_id' => 'El usuario seleccionado no es líder de semillero.'])->withInput();
            }
            if ((int) $lider->training_center_id !== (int) Auth::user()->training_center_id) {
                return back()->withErrors(['lider_id' => 'El líder debe pertenecer a tu centro de formación.'])->withInput();
            }
            if (Seedling::where('leader_id', $lider->id)->whereKeyNot($semillero->id)->exists()) {
                return back()->withErrors(['lider_id' => 'Este líder ya está vinculado a otro semillero.'])->withInput();
            }
        }

        $semillero->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? $semillero->descripcion,
            'leader_id' => $validated['lider_id'] ?? null,
        ]);

        // El modal de edición carga este formulario dentro de un <iframe> (?embedded=1).
        // Un redirect normal navegaría el listado completo DENTRO del iframe; en vez de
        // eso, se rompe el iframe para refrescar la página real completa.
        if ($request->boolean('embedded')) {
            session()->flash('success', 'Semillero actualizado correctamente.');

            return view('director_semilleros.semilleros.embedded-redirect', [
                'url' => route('dir-sem.semilleros.index'),
            ]);
        }

        return redirect()->route('dir-sem.semilleros.index')
            ->with('success', 'Semillero actualizado correctamente.');
    }

    /**
     * POST: cambiar el lider_semillero asignado
     */
    public function reasignarLider(Request $request, Seedling $semillero)
    {
        $this->authorize('semilleros.reasignar_lider');
        $this->checkCentroFormacion($semillero);

        $validated = $request->validate([
            'nuevo_lider_id' => 'required|exists:users,id',
        ]);

        $nuevoLider = User::findOrFail($validated['nuevo_lider_id']);

        if (! $nuevoLider->hasRole('lider_semillero')) {
            return redirect()->back()->with('error', 'El usuario seleccionado no tiene el rol de líder de semillero.');
        }

        if ($nuevoLider->training_center_id !== Auth::user()->training_center_id) {
            return redirect()->back()->with('error', 'El líder pertenece a otro centro de formación.');
        }
        if (Seedling::where('leader_id', $nuevoLider->id)->whereKeyNot($semillero->id)->exists()) {
            return redirect()->back()->with('error', 'Este líder ya está vinculado a otro semillero.');
        }

        $semillero->update([
            'leader_id' => $nuevoLider->id,
        ]);

        return redirect()->back()->with('success', 'Líder reasignado correctamente.');
    }

    /**
     * Activar/desactivar semillero
     */
    public function toggleEstado(Seedling $semillero)
    {
        $this->authorize('semilleros.activar_desactivar');
        $this->checkCentroFormacion($semillero);

        if ($semillero->estado === EstadoEnum::Activo) {
            // No desactivar un semillero con proyectos activos (mostrar warning)
            $proyectosActivos = $semillero->projects()->where('estado', EstadoEnum::Activo)->count();
            if ($proyectosActivos > 0) {
                return redirect()->back()->with('warning', 'No se puede desactivar el semillero porque tiene proyectos activos.');
            }
            $semillero->update(['estado' => EstadoEnum::Inactivo]);
            $mensaje = 'Semillero desactivado correctamente.';
        } else {
            $semillero->update(['estado' => EstadoEnum::Activo]);
            $mensaje = 'Semillero activado correctamente.';
        }

        return redirect()->back()->with('success', $mensaje);
    }

    /**
     * Eliminar semillero
     */
    public function destroy(Seedling $semillero)
    {
        $this->authorize('semilleros.editar');
        $this->checkCentroFormacion($semillero);

        $semillero->delete();

        return redirect()->route('dir-sem.semilleros.index')
            ->with('success', 'Semillero eliminado correctamente.');
    }

    public function descargarEvidencia(ProjectEvidence $evidencia): StreamedResponse
    {
        $this->authorize('semilleros.ver_detalle');
        $semillero = $evidencia->project?->seedling ?? abort(404);
        $this->checkCentroFormacion($semillero);

        return $this->descargarArchivoPublico($evidencia->archivo, $evidencia->nombre);
    }

    public function descargarDocumento(SeedlingFile $documento): StreamedResponse
    {
        $this->authorize('semilleros.ver_detalle');
        $this->checkCentroFormacion($documento->seedling);

        return $this->descargarArchivoPublico($documento->url_archivo, $documento->archivo);
    }

    /**
     * BUG-20260813-045 — codigo pasó de integer a string (alfanumérico), así
     * que MAX('codigo') en SQL ya no sirve para sugerir el siguiente
     * consecutivo: sobre un varchar, MySQL compara lexicográficamente, no
     * numéricamente ("9" > "10"). Se calcula el máximo numérico en PHP,
     * ignorando los códigos que ya no son puramente numéricos.
     */
    private function siguienteCodigoSugerido(): string
    {
        $maximoNumerico = Seedling::pluck('codigo')
            ->filter(fn ($c) => ctype_digit((string) $c))
            ->map(fn ($c) => (int) $c)
            ->max();

        return (string) (($maximoNumerico ?? Seedling::count()) + 1);
    }

    /**
     * Extra validación de regla de negocio "Solo gestiona semilleros del mismo centro_formacion_id"
     * Scope principal: seedlings.training_center_id. Fallback: creator.training_center_id.
     */
    private function checkCentroFormacion(Seedling $semillero): void
    {
        $user = Auth::user();
        if (! $user->training_center_id) {
            return;
        }

        $centerOfRecord = $semillero->training_center_id
            ?? $semillero->creator?->training_center_id;

        if ($centerOfRecord !== null && (int) $centerOfRecord !== (int) $user->training_center_id) {
            abort(403, 'No tienes permiso para gestionar semilleros de otros centros de formación.');
        }
    }
}
