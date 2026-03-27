<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\RoleModuleLinks;
use App\Support\SystemAdminCenterLink;
use App\Support\TrainingCenterAccess;
use Illuminate\Database\Eloquent\Builder;
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
            User::with(['person.entityPosition', 'roles', 'trainingCenter']),
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

        $usuarios = $this->scopeUsuariosElegiblesParaAsignarRol(
            TrainingCenterAccess::scopeUserQueryForList(
                User::with('person'),
                auth()->user()
            )
        )
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

        $validatedFilter = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'rol'    => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $query = TrainingCenterAccess::scopeUserQueryForList(
            User::with(['person', 'roles']),
            auth()->user()
        );

        $rolFiltro = $validatedFilter['rol'] ?? null;
        if ($rolFiltro) {
            $query->where(function (Builder $q) use ($rolFiltro) {
                $q->whereHas('roles', function (Builder $r) use ($rolFiltro) {
                    $r->where('roles.name', $rolFiltro)->where('roles.guard_name', 'web');
                })->orWhere('users.primary_role_name', $rolFiltro);
            });
        } else {
            $query->whereHas('roles');
        }

        $query->orderBy('numero_documento');

        if (! empty($validatedFilter['search'])) {
            $term = $validatedFilter['search'];
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

        // Regla de negocio: los aprendices (miembros de semillero sin rol) no son candidatos a asignación de rol.
        if (! $usuario->roles()->exists() && $usuario->seedlings()->exists()) {
            return redirect()->back()->with('error', 'No se puede asignar rol a aprendices registrados por asesor.');
        }

        TrainingCenterAccess::validateCentroBoundRoleAssignment($usuario, $validated['rol'], auth()->user());

        $hadRoles = $usuario->roles()->exists();
        RoleModuleLinks::lockPrimaryRoleBeforeAddingRole($usuario);
        $usuario->assignRole($validated['rol']);
        $this->autoAssignResearchGroup($usuario, $validated['rol']);
        if (! $hadRoles) {
            $usuario->refresh();
            $usuario->forceFill(['primary_role_name' => $validated['rol']])->saveQuietly();
        }

        if ($request->input('_from') === 'usuarios_con_rol') {
            return redirect()->route('admin.usuarios.usuarios_con_rol')->with('success', 'Rol agregado y vinculado al grupo de investigación (si aplica).');
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
        $trainingCenters = TrainingCenterAccess::isSuperAdmin(auth()->user())
            ? TrainingCenter::query()->where('activo', true)->orderBy('nombre')->get()
            : collect();

        return view('admin.usuarios.create', compact('roles', 'trainingCenters'));
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

        $actor = auth()->user();
        if (TrainingCenterAccess::isSuperAdmin($actor)) {
            $request->validate([
                'training_center_id' => ['nullable', 'exists:training_centers,id'],
            ]);
        }

        // Separar nombre y apellido en primer/segundo (para tabla people)
        [$primerNombre, $segundoNombre] = $this->splitNombreCompleto($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->splitNombreCompleto($validated['apellido']);

        // Por defecto: quien crea no es super → hereda el centro del creador (usuarios de su sede).
        // Super administrador: no copia su propio centro; elige en el formulario o queda sin centro (vinculación manual después).
        if (! TrainingCenterAccess::isSuperAdmin($actor)) {
            $trainingCenterId = $actor->training_center_id;
        } else {
            $rawTc = $request->input('training_center_id');
            $trainingCenterId = ($rawTc === null || $rawTc === '') ? null : (int) $rawTc;
            if (! empty($validated['rol'])
                && in_array($validated['rol'], ['administrador_sistema', 'admin'], true)
            ) {
                $trainingCenterId = null;
            }
            if (TrainingCenterAccess::roleRequiresTrainingCenter($validated['rol'] ?? null)
                && ($trainingCenterId === null || (int) $trainingCenterId === 0)) {
                return redirect()->back()
                    ->withErrors([
                        'training_center_id' => 'Este rol exige un centro de formación. Selecciónalo o crea el usuario y asígnalo al editar.',
                    ])
                    ->withInput();
            }
        }

        $user = User::create([
            'training_center_id' => $trainingCenterId,
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
            $this->autoAssignResearchGroup($user, $validated['rol']);
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
        $trainingCenters = TrainingCenterAccess::isSuperAdmin(auth()->user())
            ? TrainingCenter::query()->where('activo', true)->orderBy('nombre')->get()
            : collect();

        return view('admin.usuarios.edit', compact('usuario', 'roles', 'trainingCenters'));
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

        if (TrainingCenterAccess::isSuperAdmin(auth()->user())) {
            $request->validate([
                'training_center_id' => ['nullable', 'exists:training_centers,id'],
            ]);
        }

        if (TrainingCenterAccess::isSuperAdmin(auth()->user())) {
            $rawTc = $request->input('training_center_id');
            $newTcId = ($rawTc === null || $rawTc === '') ? null : (int) $rawTc;
            if (SystemAdminCenterLink::roleNameIsSystemAdministrator($validated['rol'])
                && $newTcId !== null && $newTcId !== 0
                && SystemAdminCenterLink::trainingCenterHasSystemAdmin($newTcId, $usuario->id)) {
                return redirect()->back()
                    ->withErrors([
                        'training_center_id' => 'Cada centro solo puede tener un administrador del sistema. Este centro ya está vinculado a otro usuario. Usa «Centro ↔ administrador» o deja sin centro al administrador actual.',
                    ])
                    ->withInput();
            }
        }

        // Separar nombre y apellido en primer/segundo
        [$primerNombre, $segundoNombre] = $this->splitNombreCompleto($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->splitNombreCompleto($validated['apellido']);

        $userAttrs = [
            'email'            => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
        ];
        if (TrainingCenterAccess::isSuperAdmin(auth()->user())) {
            $rawTc = $request->input('training_center_id');
            $userAttrs['training_center_id'] = ($rawTc === null || $rawTc === '') ? null : (int) $rawTc;
        }
        $usuario->update($userAttrs);
        $usuario->refresh();

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
        $this->autoAssignResearchGroup($usuario, $validated['rol']);
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
        if (! $usuario->roles()->exists() && $usuario->seedlings()->exists()) {
            return redirect()->back()->with('error', 'No se puede asignar rol a aprendices registrados por asesor.');
        }
        $hadRoles = $usuario->roles()->exists();
        RoleModuleLinks::lockPrimaryRoleBeforeAddingRole($usuario);
        $usuario->assignRole($request->rol);
        $this->autoAssignResearchGroup($usuario, $request->rol);
        if (! $hadRoles) {
            $usuario->refresh();
            $usuario->forceFill(['primary_role_name' => $request->rol])->saveQuietly();
        }

        return redirect()->back()->with('success', 'Rol asignado exitosamente y grupo vinculado.');
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

    /**
     * Autovincula al usuario al grupo de investigación de su centro si adquiere rol investigativo.
     */
    private function autoAssignResearchGroup(User $usuario, string $rol): void
    {
        if (!in_array($rol, ['director_investigacion', 'investigador_asociado'], true)) {
            return;
        }

        if (!$usuario->training_center_id) {
            return;
        }

        $researchGroup = \App\Models\ResearchGroup::where('training_center_id', $usuario->training_center_id)->first();
        if (!$researchGroup) {
            return;
        }

        $rolGrupo = $rol === 'director_investigacion'
            ? \App\Enums\RolGrupoEnum::Director
            : \App\Enums\RolGrupoEnum::InvestigadorAsociado;

        \App\Models\ResearchGroupUser::firstOrCreate(
            [
                'research_group_id' => $researchGroup->id,
                'user_id' => $usuario->id,
            ],
            [
                'rol' => $rolGrupo,
            ]
        );
    }

    /**
     * Candidatos válidos para asignar rol:
     * - sin roles actuales
     * - excluye aprendices del semillero (usuarios vinculados como miembros).
     */
    private function scopeUsuariosElegiblesParaAsignarRol(Builder $query): Builder
    {
        return $query
            ->whereDoesntHave('roles')
            ->whereDoesntHave('seedlings');
    }
}
