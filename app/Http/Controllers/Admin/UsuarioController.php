<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the users.
     * Permission: usuarios.listar
     */
    public function index(Request $request)
    {
        $this->authorize('usuarios.listar');

        $query = User::with(['person', 'roles'])
            ->where('training_center_id', auth()->user()->training_center_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%")
                  ->orWhereHas('person', function ($q2) use ($search) {
                      $q2->where('primer_nombre', 'like', "%{$search}%")
                         ->orWhere('primer_apellido', 'like', "%{$search}%")
                         ->orWhere('segundo_nombre', 'like', "%{$search}%")
                         ->orWhere('segundo_apellido', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('rol')) {
            $query->role($request->rol);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $usuarios = $query->paginate(15)->withQueryString();
        $roles = Role::all();
        $estados = EstadoEnum::cases();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'estados'));
    }

    /**
     * Show the form for creating a new user.
     * Permission: usuarios.crear
     */
    public function create()
    {
        $this->authorize('usuarios.crear');
        
        $roles = Role::all();
        return view('admin.usuarios.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     * Permission: usuarios.crear
     */
    public function store(Request $request)
    {
        $this->authorize('usuarios.crear');

        $validated = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido'         => 'required|string|max:100',
            'numero_documento' => 'required|string|max:20|unique:users,numero_documento',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:8',
            'rol'              => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'training_center_id' => auth()->user()->training_center_id,
            'email'              => $validated['email'],
            'numero_documento'   => $validated['numero_documento'],
            'tipo_documento'     => 'CC', // Default, assuming CC. Can be updated if form has it
            'password'           => Hash::make($validated['password']),
            'estado'             => EstadoEnum::Activo,
        ]);

        // Guardar Persona
        Person::create([
            'user_id'         => $user->id,
            'primer_nombre'   => $validated['nombre'],
            'primer_apellido' => $validated['apellido'],
            'email_institucional' => $validated['email'],
        ]);

        // Asignar Rol
        $user->assignRole($validated['rol']);

        // Enviar credenciales (Si tiene permiso usuarios.asignar_credenciales)
        if (auth()->user()->can('usuarios.asignar_credenciales')) {
            // Nota: Aquí se debería crear un Mailable real
            // Mail::to($user->email)->send(new \App\Mail\UserCredentialsMail($user, $validated['password']));
        }

        return redirect()->route('admin.usuarios.index')
                         ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Show the form for editing the specified user.
     * Permission: usuarios.editar
     */
    public function edit($id)
    {
        $this->authorize('usuarios.editar');

        $usuario = User::where('training_center_id', auth()->user()->training_center_id)->findOrFail($id);
        $roles = Role::all();

        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Update the specified user in storage.
     * Permission: usuarios.editar
     */
    public function update(Request $request, $id)
    {
        $this->authorize('usuarios.editar');

        $usuario = User::where('training_center_id', auth()->user()->training_center_id)->findOrFail($id);

        $validated = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido'         => 'required|string|max:100',
            'numero_documento' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($usuario->id)],
            'email'            => ['required', 'email', Rule::unique('users')->ignore($usuario->id)],
            'rol'              => 'required|exists:roles,name',
        ]);

        $usuario->update([
            'email'            => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
        ]);

        // Actualizar Persona
        if ($usuario->person) {
            $usuario->person->update([
                'primer_nombre'   => $validated['nombre'],
                'primer_apellido' => $validated['apellido'],
                'email_institucional' => $validated['email'],
            ]);
        } else {
            Person::create([
                'user_id'         => $usuario->id,
                'primer_nombre'   => $validated['nombre'],
                'primer_apellido' => $validated['apellido'],
                'email_institucional' => $validated['email'],
            ]);
        }

        // Sincronizar Rol
        $usuario->syncRoles([$validated['rol']]);

        return redirect()->route('admin.usuarios.index')
                         ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Toggle active/inactive status.
     * Permission: usuarios.activar_desactivar
     */
    public function toggleEstado($id)
    {
        $this->authorize('usuarios.activar_desactivar');

        $usuario = User::where('training_center_id', auth()->user()->training_center_id)->findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        if ($usuario->estado === EstadoEnum::Activo) {
            $usuario->estado = EstadoEnum::Inactivo;
        } else {
            $usuario->estado = EstadoEnum::Activo;
        }
        
        $usuario->save();

        return redirect()->back()->with('success', 'Estado del usuario actualizado.');
    }

    /**
     * Assign a role to a user.
     * Permission: usuarios.asignar_rol
     */
    public function asignarRol(Request $request, $id)
    {
        $this->authorize('usuarios.asignar_rol');

        $request->validate(['rol' => 'required|exists:roles,name']);

        $usuario = User::where('training_center_id', auth()->user()->training_center_id)->findOrFail($id);
        $usuario->assignRole($request->rol);

        return redirect()->back()->with('success', 'Rol asignado correctamente.');
    }

    /**
     * Revoke a role from a user.
     * Permission: usuarios.revocar_rol
     */
    public function revocarRol(Request $request, $id)
    {
        $this->authorize('usuarios.revocar_rol');

        $request->validate(['rol' => 'required|exists:roles,name']);

        $usuario = User::where('training_center_id', auth()->user()->training_center_id)->findOrFail($id);
        
        if ($usuario->id === auth()->id() && $request->rol === 'administrador_sistema') {
            return redirect()->back()->with('error', 'No puedes revocar tu propio rol de administrador.');
        }

        $usuario->removeRole($request->rol);

        return redirect()->back()->with('success', 'Rol revocado correctamente.');
    }
}
