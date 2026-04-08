<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\ResearchGroup;
use App\Models\Seedling;
use App\Models\User;
use App\Enums\EstadoEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SemilleroController extends Controller
{
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

        $query = Seedling::with(['leader.person', 'members', 'researchGroup', 'creator'])
            ->where(function ($q) use ($user) {
                if ($user->training_center_id) {
                    $q->whereHas('leader', function ($leaderQuery) use ($user) {
                        $leaderQuery->where('training_center_id', $user->training_center_id);
                    })->orWhereHas('creator', function ($creatorQuery) use ($user) {
                        $creatorQuery->where('training_center_id', $user->training_center_id);
                    });
                }
            });

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

        $gruposInvestigacion = ResearchGroup::query()
            ->when($user->training_center_id, fn ($q) => $q->where('training_center_id', $user->training_center_id))
            ->active()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        $siguienteCodigo = (int) Seedling::max('codigo') + 1;

        return view('director_semilleros.semilleros.index', compact('semilleros', 'tab', 'lideres', 'gruposInvestigacion', 'siguienteCodigo'));
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

        $gruposInvestigacion = ResearchGroup::query()
            ->when($user->training_center_id, fn ($q) => $q->where('training_center_id', $user->training_center_id))
            ->active()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        $siguienteCodigo = (int) Seedling::max('codigo') + 1;

        return view('director_semilleros.semilleros.create', compact('lideres', 'gruposInvestigacion', 'siguienteCodigo'));
    }

    /**
     * Validar + crear semillero
     */
    public function store(Request $request)
    {
        $this->authorize('semilleros.crear');

        $request->merge([
            'codigo'            => $request->input('codigo') ?: null,
            'research_group_id' => $request->input('research_group_id') ?: null,
        ]);

        $validated = $request->validate([
            'nombre'               => 'required|string|max:150',
            'codigo'               => 'nullable|integer|min:1',
            'descripcion'          => 'nullable|string',
            'lider_id'             => 'nullable|exists:users,id',
            'research_group_id'    => 'nullable|exists:research_groups,id',
            'logo'                 => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
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
        if (! empty($validated['research_group_id'])) {
            $grupo = ResearchGroup::find($validated['research_group_id']);
            if (! $grupo || (int) $grupo->training_center_id !== (int) Auth::user()->training_center_id) {
                return back()->withErrors(['research_group_id' => 'El grupo de investigación debe pertenecer a tu centro.'])->withInput();
            }
        }

        $codigo = !empty($validated['codigo']) ? (int) $validated['codigo'] : (int) Seedling::max('codigo') + 1;

        $logoPath = '';
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('semilleros/logos', 'public');
        }

        Seedling::create([
            'creator_id'         => Auth::id(),
            'leader_id'          => $validated['lider_id'] ?? null,
            'research_group_id'  => $validated['research_group_id'] ?? null,
            'nombre'             => $validated['nombre'],
            'codigo'             => $codigo,
            'logo'               => $logoPath,
            'descripccion'       => $validated['descripcion'] ?? null,
            'estado'             => EstadoEnum::Activo,
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
        
        return view('director_semilleros.semilleros.show', compact('semillero'));
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
                    ->orWhereKey($semillero->leader_id);
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
            'nombre'      => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'lider_id'    => 'nullable|exists:users,id',
            'logo'        => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
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

        $logoPath = $semillero->logo;
        if ($request->hasFile('logo')) {
            if (!empty($semillero->logo)) {
                Storage::disk('public')->delete($semillero->logo);
            }
            $logoPath = $request->file('logo')->store('semilleros/logos', 'public');
        }

        $semillero->update([
            'nombre'       => $validated['nombre'],
            'descripccion' => $validated['descripcion'] ?? $semillero->descripccion,
            'leader_id'    => $validated['lider_id'] ?? null,
            'logo'         => $logoPath,
        ]);

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
        
        if (!$nuevoLider->hasRole('lider_semillero')) {
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

    /**
     * Extra validación de regla de negocio "Solo gestiona semilleros del mismo centro_formacion_id"
     */
    private function checkCentroFormacion(Seedling $semillero)
    {
        $user = Auth::user();
        $leaderCenterId = $semillero->leader?->training_center_id;
        $creatorCenterId = $semillero->creator?->training_center_id;

        if (
            ($leaderCenterId !== null && $leaderCenterId !== $user->training_center_id)
            || ($leaderCenterId === null && $creatorCenterId !== null && $creatorCenterId !== $user->training_center_id)
        ) {
            abort(403, 'No tienes permiso para gestionar semilleros de otros centros de formación.');
        }
    }
}
