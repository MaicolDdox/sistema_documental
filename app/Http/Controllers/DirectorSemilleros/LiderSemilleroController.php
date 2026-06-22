<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Http\Controllers\Controller;
use App\Models\Seedling;
use App\Models\User;
use App\Services\Admin\NotificacionService;
use App\Services\Admin\UserCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LiderSemilleroController extends Controller
{
    public function __construct(
        private readonly UserCreationService $userCreation,
        private readonly NotificacionService $notificacion,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('usuarios.listar');

        $user = Auth::user();

        $query = User::role('lider_semillero')
            ->with(['person', 'ledSeedlings' => fn ($q) => $q->select('id', 'nombre', 'leader_id')])
            ->where('training_center_id', $user->training_center_id);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('email', 'like', "%{$term}%")
                    ->orWhere('numero_documento', 'like', "%{$term}%")
                    ->orWhereHas('person', function ($p) use ($term) {
                        $p->where('primer_nombre', 'like', "%{$term}%")
                            ->orWhere('primer_apellido', 'like', "%{$term}%");
                    });
            });
        }

        $lideres = $query->orderBy('email')->paginate(10)->withQueryString();

        $semilleros = $this->semillerosDisponiblesParaNuevoLider($user);

        return view('director_semilleros.lideres.index', compact('lideres', 'semilleros'));
    }

    public function create()
    {
        $this->authorize('usuarios.crear_lider_semillero');

        $user = Auth::user();

        $semilleros = $this->semillerosDisponiblesParaNuevoLider($user);

        return view('director_semilleros.lideres.create', compact('semilleros'));
    }

    public function store(Request $request)
    {
        $this->authorize('usuarios.crear_lider_semillero');

        $validated = $request->validate([
            'nombre'              => 'required|string|max:100',
            'apellido'            => 'required|string|max:100',
            'tipo_documento'      => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento'    => 'required|unique:users,numero_documento',
            'email'               => 'required|email|unique:users,email',
            'semillero_id'        => 'nullable|exists:seedlings,id',
            'enviar_credenciales' => 'nullable|boolean',
        ]);

        $password = \Illuminate\Support\Str::random(10);

        $newUser = $this->userCreation->crearUsuario([
            'email'            => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
            'tipo_documento'   => $validated['tipo_documento'],
            'password'         => $password,
            'primer_nombre'    => $validated['nombre'],
            'primer_apellido'  => $validated['apellido'],
            'rol'              => 'lider_semillero',
        ], Auth::user()->training_center_id);

        // Asignar a semillero si se seleccionó (solo sin líder previo y del mismo centro)
        if (!empty($validated['semillero_id'])) {
            $semillero = $this->semilleroDisponibleParaAsignar(
                (int) $validated['semillero_id'],
                Auth::user()
            );
            if (!$semillero) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'semillero_id' => 'El semillero seleccionado no está disponible o ya tiene líder asignado.',
                    ]);
            }
            $semillero->update(['leader_id' => $newUser->id]);
        }

        $puedeEnviarCorreo = $request->boolean('enviar_credenciales') && (
            Auth::user()->can('usuarios.asignar_credenciales')
            || Auth::user()->can('usuarios.crear_lider_semillero')
        );

        if ($puedeEnviarCorreo) {
            $this->notificacion->enviarCredenciales($newUser, $password);
        }

        $redirect = redirect()->route('dir-sem.lideres.index')
            ->with('success', 'Líder de semillero creado exitosamente.')
            ->with('credenciales', [
                'email' => $newUser->email,
                'password' => $password,
            ]);

        return $redirect;
    }

    public function show(User $lider)
    {
        $this->authorize('usuarios.listar');
        $this->ensureLeaderOfCenter($lider);

        $lider->load(['person', 'ledSeedlings']);

        return view('director_semilleros.lideres.show', compact('lider'));
    }

    public function edit(User $lider)
    {
        $this->authorize('usuarios.editar');
        $this->ensureLeaderOfCenter($lider);

        $semilleros = \App\Models\Seedling::whereHas('leader', fn ($q) => $q->where('training_center_id', Auth::user()->training_center_id))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('director_semilleros.lideres.edit', compact('lider', 'semilleros'));
    }

    public function update(Request $request, User $lider)
    {
        $this->authorize('usuarios.editar');
        $this->ensureLeaderOfCenter($lider);

        $validated = $request->validate([
            'primer_nombre'     => 'required|string|max:100',
            'primer_apellido'   => 'required|string|max:100',
            'email'             => 'required|email|unique:users,email,' . $lider->id,
            'numero_documento'  => 'required|unique:users,numero_documento,' . $lider->id,
            'estado'            => 'required|in:activo,inactivo',
        ]);

        $lider->update([
            'email'           => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
            'estado'           => EstadoEnum::from($validated['estado']),
        ]);

        if ($lider->person) {
            $lider->person->update([
                'primer_nombre'   => $validated['primer_nombre'],
                'primer_apellido' => $validated['primer_apellido'],
            ]);
        }

        return redirect()->route('dir-sem.lideres.index')->with('success', 'Líder actualizado correctamente.');
    }

    public function destroy(User $lider)
    {
        $this->authorize('usuarios.editar');
        $this->ensureLeaderOfCenter($lider);

        $lider->delete();

        return redirect()->route('dir-sem.lideres.index')->with('success', 'Líder eliminado correctamente.');
    }

    public function toggleEstado(User $lider)
    {
        $this->authorize('usuarios.editar');
        $this->ensureLeaderOfCenter($lider);

        $lider->update([
            'estado' => $lider->estado === EstadoEnum::Activo ? EstadoEnum::Inactivo : EstadoEnum::Activo,
        ]);

        $msg = $lider->estado === EstadoEnum::Activo ? 'Líder activado.' : 'Líder desactivado.';
        return redirect()->route('dir-sem.lideres.index')->with('success', $msg);
    }

    private function ensureLeaderOfCenter(User $lider): void
    {
        if (!$lider->hasRole('lider_semillero') || $lider->training_center_id !== Auth::user()->training_center_id) {
            abort(403, 'No tienes permiso para gestionar este usuario.');
        }
    }

    /**
     * Semilleros del centro sin líder asignado (evita reemplazar al líder actual al registrar uno nuevo).
     */
    private function semillerosDisponiblesParaNuevoLider(User $director)
    {
        return Seedling::query()
            ->whereNull('leader_id')
            ->whereHas('researchGroup', fn ($q) => $q->where('training_center_id', $director->training_center_id))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function semilleroDisponibleParaAsignar(int $semilleroId, User $director): ?Seedling
    {
        return Seedling::query()
            ->whereKey($semilleroId)
            ->whereNull('leader_id')
            ->whereHas('researchGroup', fn ($q) => $q->where('training_center_id', $director->training_center_id))
            ->first();
    }
}
