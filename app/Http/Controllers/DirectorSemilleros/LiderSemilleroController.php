<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\EstadoEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LiderSemilleroController extends Controller
{
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

        $semilleros = \App\Models\Seedling::whereHas('researchGroup', fn ($q) => $q->where('training_center_id', $user->training_center_id))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('director_semilleros.lideres.index', compact('lideres', 'semilleros'));
    }

    public function create()
    {
        $this->authorize('usuarios.crear_lider_semillero');

        $user = Auth::user();
        
        // semilleros del centro para el select
        $semilleros = \App\Models\Seedling::whereHas('leader', function($q) use ($user) {
            $q->where('training_center_id', $user->training_center_id);
        })->get();

        return view('director_semilleros.lideres.create', compact('semilleros'));
    }

    public function store(Request $request)
    {
        $this->authorize('usuarios.crear_lider_semillero');

        $validated = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido'         => 'required|string|max:100',
            'numero_documento' => 'required|unique:users,numero_documento',
            'email'            => 'required|email|unique:users,email',
            'semillero_id'     => 'nullable|exists:seedlings,id',
            'enviar_credenciales' => 'nullable|boolean',
        ]);

        $password = Str::random(10);

        // Crear usuario principal
        // Nota: En la DB actual hay models User y Person. Dependiendo de cómo the project handles signup.
        // Asumiendo llenar los fillables de User
        // User: training_center_id, email, tipo_documento, numero_documento, password, estado
        // Los nombres (primer_nombre, etc.) van a Person
        $newUser = User::create([
            'training_center_id' => Auth::user()->training_center_id,
            'email'              => $validated['email'],
            'numero_documento'   => $validated['numero_documento'],
            'tipo_documento'     => \App\Enums\TipoDocumentoEnum::CedulaCiudadana,
            'password'           => Hash::make($password),
            'estado'             => EstadoEnum::Activo,
        ]);

        // Crear persona asociada (campos requeridos de people sin valor en el formulario)
        $newUser->person()->create([
            'primer_nombre'        => $validated['nombre'],
            'primer_apellido'      => $validated['apellido'],
            'segundo_apellido'     => '',
            'email_institucional' => $validated['email'],
            'genero'               => 'prefiero no decirlo',
            'celular'             => 0,
            'eps'                 => '',
        ]);

        // Asignar rol
        $newUser->assignRole('lider_semillero');

        // Asignar a semillero si se seleccionó
        if (!empty($validated['semillero_id'])) {
            $semillero = \App\Models\Seedling::find($validated['semillero_id']);
            if ($semillero) {
                $semillero->update(['leader_id' => $newUser->id]);
            }
        }

        // Si tiene permiso y marcó checkbox
        if ($request->has('enviar_credenciales') && Auth::user()->can('usuarios.asignar_credenciales')) {
            // Enviar credenciales
            try {
                Mail::raw("Tus credenciales de acceso son: Email: {$newUser->email} y Contraseña: {$password}", function ($message) use ($newUser) {
                    $message->to($newUser->email)->subject('Credenciales de acceso GIDESTH');
                });
            } catch (\Exception $e) {
                // ignorar error si mail no está config
            }
        }

        return redirect()->route('dir-sem.lideres.index')
            ->with('success', 'Líder de semillero creado exitosamente.');
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
}
