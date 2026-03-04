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
    public function index()
    {
        $this->authorize('usuarios.listar');

        $user = Auth::user();

        // lista de usuarios con rol lider_semillero del centro
        $lideres = User::role('lider_semillero')
            ->where('training_center_id', $user->training_center_id)
            ->paginate(10);

        return view('director_semilleros.lideres.index', compact('lideres'));
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
            'tipo_documento'     => \App\Enums\TipoDocumentoEnum::CC, // Default as we didn't ask for it
            'password'           => Hash::make($password),
            'estado'             => EstadoEnum::Activo,
        ]);

        // Crear persona asociada
        $newUser->person()->create([
            'primer_nombre'   => $validated['nombre'],
            'primer_apellido' => $validated['apellido'],
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
}
