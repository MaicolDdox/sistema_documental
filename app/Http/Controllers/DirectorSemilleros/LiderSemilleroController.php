<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Http\Controllers\Controller;
use App\Models\Seedling;
use App\Models\User;
use App\Enums\EstadoEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
        $newUser->forceFill(['primary_role_name' => 'lider_semillero'])->saveQuietly();

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

        $advertenciaMail = null;
        $quiereCorreo = $request->boolean('enviar_credenciales');
        $puedeEnviarCorreo = $quiereCorreo && (
            Auth::user()->can('usuarios.asignar_credenciales')
            || Auth::user()->can('usuarios.crear_lider_semillero')
        );

        if ($puedeEnviarCorreo) {
            try {
                Mail::raw(
                    "Bienvenido al sistema GIDESTH.\n\nTus credenciales de acceso:\n\nCorreo: {$newUser->email}\nContraseña temporal: {$password}\n\nPor favor cambia tu contraseña al ingresar por primera vez.",
                    function ($message) use ($newUser) {
                        $message->to($newUser->email)->subject('Credenciales de acceso — GIDESTH');
                    }
                );
            } catch (\Throwable $e) {
                Log::error('LiderSemillero: fallo al enviar credenciales por correo', [
                    'nuevo_usuario_id' => $newUser->id,
                    'email' => $newUser->email,
                    'error' => $e->getMessage(),
                ]);
                $advertenciaMail = 'El líder se registró correctamente, pero no se pudo enviar el correo con la contraseña. Revise la configuración de correo (MAIL_*) en el servidor o consulte al administrador.';
            }
        }

        $redirect = redirect()->route('dir-sem.lideres.index')
            ->with('success', 'Líder de semillero creado exitosamente.');

        if ($advertenciaMail !== null) {
            $redirect->with('warning', $advertenciaMail);
        }

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
