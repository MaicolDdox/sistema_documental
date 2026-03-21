<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\User;
use App\Support\RoleModuleLinks;
use App\Support\TrainingCenterAccess;
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

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $auth = $request->user();
        $query = TrainingCenterAccess::scopeUserQueryForList(
            User::with(['person.entityPosition', 'roles']),
            $auth
        );

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
     * Roles que el usuario actual puede asignar (para chulitos y filtrado del select).
     * - Administrador (usuarios.asignar_rol): todos.
     * - Director semilleros (usuarios.crear_lider_semillero): lider_semillero.
     * - Líder semillero: asesor_semillero.
     * - Director investigación (usuarios.crear_investigador_asociado): investigador_asociado.
     */
    protected function getAssignableRoleNames(): array
    {
        $user = auth()->user();
        if ($user->can('usuarios.asignar_rol')) {
            return Role::orderBy('name')->pluck('name')->all();
        }
        $names = [];
        if ($user->can('usuarios.crear_lider_semillero')) {
            $names[] = 'lider_semillero';
        }
        if ($user->hasRole('lider_semillero')) {
            $names[] = 'asesor_semillero';
        }
        if ($user->can('usuarios.crear_investigador_asociado')) {
            $names[] = 'investigador_asociado';
        }
        return array_values(array_unique($names));
    }

    /**
     * Vista: Asignación de roles (formulario usuario + rol).
     * Permission: usuarios.asignar_rol
     */
    public function asignarRoles()
    {
        $this->authorize('usuarios.asignar_rol');

        $usuarios = TrainingCenterAccess::scopeUserQueryForList(
            User::with('person'),
            auth()->user()
        )
            ->whereDoesntHave('roles')
            ->orderBy('numero_documento')
            ->get();
        $roles = Role::with('permissions')->orderBy('name')->get();
        $roleNamesAssignable = $this->getAssignableRoleNames();

        return view('admin.usuarios.asignar_roles', compact('usuarios', 'roles', 'roleNamesAssignable'));
    }

    /**
     * Vista: Usuarios que ya tienen rol — listado y opción de agregar otro rol.
     * Permission: usuarios.asignar_rol
     */
    public function usuariosConRol(Request $request)
    {
        $this->authorize('usuarios.asignar_rol');

        $query = TrainingCenterAccess::scopeUserQueryForList(
            User::with(['person', 'roles']),
            auth()->user()
        )
            ->whereHas('roles')
            ->orderBy('numero_documento');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('numero_documento', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhereHas('person', function ($q2) use ($term) {
                      $q2->where('primer_nombre', 'like', "%{$term}%")
                         ->orWhere('primer_apellido', 'like', "%{$term}%");
                  });
            });
        }

        $usuarios = $query->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();
        $roleNamesAssignable = $this->getAssignableRoleNames();
        $primaryRoleNamesByUserId = RoleModuleLinks::primaryRoleNamesByUserIds(
            $usuarios->getCollection()->pluck('id')->all()
        );

        return view('admin.usuarios.usuarios_con_rol', compact(
            'usuarios',
            'roles',
            'roleNamesAssignable',
            'primaryRoleNamesByUserId'
        ));
    }

    /**
     * Guardar asignación de rol desde el formulario de la página Asignar Roles o Usuarios con rol.
     * Solo se permite asignar roles que el usuario actual tiene permiso para asignar.
     */
    public function storeAsignarRol(Request $request)
    {
        $assignable = $this->getAssignableRoleNames();
        if (empty($assignable)) {
            abort(403, 'No tiene permiso para asignar roles.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'rol'     => ['required', 'string', 'exists:roles,name', 'in:' . implode(',', $assignable)],
        ]);

        $usuario = TrainingCenterAccess::scopeUserQueryForList(User::query(), auth()->user())
            ->findOrFail($validated['user_id']);
        TrainingCenterAccess::validateCentroBoundRoleAssignment($usuario, $validated['rol'], auth()->user());

        $hadRoles = $usuario->roles()->exists();
        RoleModuleLinks::lockPrimaryRoleBeforeAddingRole($usuario);
        $usuario->assignRole($validated['rol']);
        if (! $hadRoles) {
            $usuario->refresh();
            $usuario->forceFill(['primary_role_name' => $validated['rol']])->saveQuietly();
        }

        if ($request->input('_from') === 'usuarios_con_rol') {
            return redirect()->route('admin.usuarios.usuarios_con_rol')->with('success', 'Rol agregado correctamente.');
        }
        return redirect()->route('admin.usuarios.asignar_roles')->with('success', 'Rol asignado correctamente.');
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
            'rol'              => 'nullable|exists:roles,name',
        ]);

        // Separar nombre y apellido en primer/segundo (para tabla people)
        [$primerNombre, $segundoNombre] = $this->splitNombreCompleto($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->splitNombreCompleto($validated['apellido']);

        $user = User::create([
            'training_center_id' => auth()->user()->training_center_id,
            'email'              => $validated['email'],
            'numero_documento'   => $validated['numero_documento'],
            // Usamos el valor del enum por defecto: cédula de ciudadanía
            'tipo_documento'     => \App\Enums\TipoDocumentoEnum::CedulaCiudadana->value,
            'password'           => Hash::make($validated['password']),
            'estado'             => EstadoEnum::Activo,
        ]);

        // Guardar Persona (perfil mínimo; otros campos podrán completarse luego)
        Person::create([
            'user_id'             => $user->id,
            'primer_nombre'       => $primerNombre,
            'segundo_nombre'      => $segundoNombre,
            'primer_apellido'     => $primerApellido,
            'segundo_apellido'    => $segundoApellido,
            'email_institucional' => $validated['email'],
        ]);

        // Asignar rol solo si se envió
        if (! empty($validated['rol'])) {
            $user->assignRole($validated['rol']);
            $user->refresh();
            $user->forceFill(['primary_role_name' => $validated['rol']])->saveQuietly();
        }

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

        $usuario = $this->findUserScoped((int) $id);
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

        $usuario = $this->findUserScoped((int) $id);

        $validated = $request->validate([
            'nombre'           => 'required|string|max:100',
            'apellido'         => 'required|string|max:100',
            'numero_documento' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($usuario->id)],
            'email'            => ['required', 'email', Rule::unique('users')->ignore($usuario->id)],
            'rol'              => 'required|exists:roles,name',
        ]);

        // Separar nombre y apellido en primer/segundo
        [$primerNombre, $segundoNombre] = $this->splitNombreCompleto($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->splitNombreCompleto($validated['apellido']);

        $usuario->update([
            'email'            => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
        ]);

        // Actualizar Persona
        if ($usuario->person) {
            $usuario->person->update([
                'primer_nombre'       => $primerNombre,
                'segundo_nombre'      => $segundoNombre,
                'primer_apellido'     => $primerApellido,
                'segundo_apellido'    => $segundoApellido,
                'email_institucional' => $validated['email'],
            ]);
        } else {
            Person::create([
                'user_id'              => $usuario->id,
                'primer_nombre'        => $primerNombre,
                'segundo_nombre'       => $segundoNombre,
                'primer_apellido'      => $primerApellido,
                'segundo_apellido'     => $segundoApellido,
                'email_institucional'  => $validated['email'],
                'entity_position_id'   => \App\Models\EntityPosition::first()?->id,
                'linkage_type_id'      => \App\Models\LinkageType::first()?->id,
                'training_program_id'  => \App\Models\TrainingProgram::first()?->id,
                'genero'               => 'prefiero no decirlo',
                'celular'              => 0,
                'eps'                  => '',
            ]);
        }

        TrainingCenterAccess::validateCentroBoundRoleAssignment($usuario, $validated['rol'], auth()->user());

        // Asegurar el rol elegido sin quitar otros roles (evitar syncRoles).
        if (! $usuario->hasRole($validated['rol'])) {
            $usuario->assignRole($validated['rol']);
        }
        $usuario->primary_role_name = $validated['rol'];
        $usuario->save();

        return redirect()->route('admin.usuarios.index')
                         ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Separa un nombre/apellido completo en primer y segundo componente.
     * Ej: "Juan Carlos" => ["Juan", "Carlos"], "Pérez" => ["Pérez", null]
     */
    private function findUserScoped(int $id): User
    {
        return TrainingCenterAccess::scopeUserQueryForList(User::query(), auth()->user())
            ->findOrFail($id);
    }

    private function splitNombreCompleto(string $valor): array
    {
        $valor = trim(preg_replace('/\s+/', ' ', $valor));
        if ($valor === '') {
            // Devolvemos cadenas vacías para evitar problemas con columnas NOT NULL
            return ['', ''];
        }
        $partes = explode(' ', $valor, 2);
        $primer = $partes[0] ?? '';
        $segundo = $partes[1] ?? '';
        return [$primer, $segundo];
    }

    /**
     * Toggle active/inactive status.
     * Permission: usuarios.activar_desactivar
     */
    public function toggleEstado($id)
    {
        $this->authorize('usuarios.activar_desactivar');

        $usuario = $this->findUserScoped((int) $id);

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

        $usuario = $this->findUserScoped((int) $id);
        $hadRoles = $usuario->roles()->exists();
        RoleModuleLinks::lockPrimaryRoleBeforeAddingRole($usuario);
        $usuario->assignRole($request->rol);
        if (! $hadRoles) {
            $usuario->refresh();
            $usuario->forceFill(['primary_role_name' => $request->rol])->saveQuietly();
        }

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

        $usuario = $this->findUserScoped((int) $id);

        if ($usuario->id === auth()->id() && in_array($request->rol, ['administrador_sistema', 'super_administrador'], true)) {
            return redirect()->back()->with('error', 'No puedes revocar tu propio rol de administrador.');
        }

        $clearPrimary = $usuario->primary_role_name === $request->rol;
        $usuario->removeRole($request->rol);
        if ($clearPrimary) {
            $usuario->primary_role_name = null;
            $usuario->saveQuietly();
        }

        return redirect()->back()->with('success', 'Rol revocado correctamente.');
    }

    /**
     * Remove the specified user.
     * Permission: usuarios.editar (quien puede editar puede eliminar)
     */
    public function destroy($id)
    {
        $this->authorize('usuarios.editar');

        $usuario = $this->findUserScoped((int) $id);

        if ($usuario->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->syncRoles([]);
        $usuario->person?->delete();
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
