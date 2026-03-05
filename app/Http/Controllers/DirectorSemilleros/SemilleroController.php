<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\ResearchGroup;
use App\Models\Seedling;
use App\Models\User;
use App\Enums\EstadoEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $query = Seedling::with(['leader.person', 'members', 'researchGroup'])
            ->whereHas('leader', function ($q) use ($user) {
                if ($user->training_center_id) {
                    $q->where('training_center_id', $user->training_center_id);
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

        return view('director_semilleros.semilleros.index', compact('semilleros', 'tab'));
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
            'lider_id'             => 'required|exists:users,id',
            'research_group_id'    => 'nullable|exists:research_groups,id',
        ]);

        $codigo = !empty($validated['codigo']) ? (int) $validated['codigo'] : (int) Seedling::max('codigo') + 1;

        Seedling::create([
            'creator_id'         => Auth::id(),
            'leader_id'          => $validated['lider_id'],
            'research_group_id'  => $validated['research_group_id'] ?? null,
            'nombre'             => $validated['nombre'],
            'codigo'             => $codigo,
            'logo'               => '',
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
            'lider_id'    => 'required|exists:users,id',
        ]);

        $semillero->update([
            'nombre'       => $validated['nombre'],
            'descripccion' => $validated['descripcion'] ?? $semillero->descripccion,
            'leader_id'    => $validated['lider_id'],
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
        if ($semillero->leader && $semillero->leader->training_center_id !== $user->training_center_id) {
            abort(403, 'No tienes permiso para gestionar semilleros de otros centros de formación.');
        }
    }
}
