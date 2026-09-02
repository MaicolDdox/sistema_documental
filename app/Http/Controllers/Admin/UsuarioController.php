<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\User;
use App\Services\Admin\NotificacionService;
use App\Services\Admin\UserCreationService;
use App\Support\SystemAdminCenterLink;
use App\Support\TrainingCenterAccess;
use App\Support\UserOwnershipAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly UserCreationService $userCreation,
        private readonly NotificacionService $notificacion,
    ) {}

    /**
     * Display a listing of the users.
     * Permission: usuarios.listar
     */
    public function index(Request $request)
    {
        $this->authorize('usuarios.listar');

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
        $roles = Role::whereIn('name', \App\Support\RoleModuleLinks::LOGIN_ROLE_PRIORITY)->orderBy('name')->get();
        $rolesAsignables = Role::whereIn('name', $this->assignableRoles())->orderBy('name')->get();
        $estados = EstadoEnum::cases();
        // Este listado ya excluye al propio usuario que consulta
        // (scopeUserQueryForList), así que cada fila es siempre "otro
        // usuario" — gestionar sus roles adicionales siempre está permitido.
        $canManageAdditionalRolesModal = true;

        return view('admin.usuarios.index', compact(
            'usuarios', 'roles', 'rolesAsignables', 'estados', 'canManageAdditionalRolesModal',
        ));
    }

    /**
     * Roles que administrador_sistema puede crear/asignar (matriz de creación exclusiva).
     */
    private function assignableRoles(): array
    {
        return \App\Support\RoleAssignmentMatrix::assignableRolesFor(auth()->user());
    }

    /**
     * Formulario de creación exclusivo para director_semilleros (rol fijo, sin selector).
     */
    public function createDirectorSemilleros()
    {
        if (! auth()->user()->can('usuarios.crear_director_semilleros')) {
            abort(403);
        }

        $rolesAdicionales = Role::whereIn(
            'name',
            \App\Support\RoleAssignmentMatrix::additionalRoleOptionNamesFor('director_semilleros')
        )->orderBy('name')->get();

        return view('admin.director_semilleros.create', compact('rolesAdicionales'));
    }

    public function storeDirectorSemilleros(Request $request)
    {
        return $this->storeConRolFijo($request, 'director_semilleros', 'usuarios.crear_director_semilleros');
    }

    /**
     * Formulario de creación exclusivo para co_investigador (rol fijo, sin selector).
     */
    public function createCoinvestigador()
    {
        if (! auth()->user()->can('usuarios.crear_co_investigador')) {
            abort(403);
        }

        $rolesAdicionales = Role::whereIn(
            'name',
            \App\Support\RoleAssignmentMatrix::additionalRoleOptionNamesFor('co_investigador')
        )->orderBy('name')->get();

        return view('admin.coinvestigadores.create', compact('rolesAdicionales'));
    }

    public function storeCoinvestigador(Request $request)
    {
        return $this->storeConRolFijo($request, 'co_investigador', 'usuarios.crear_co_investigador');
    }

    /**
     * Lógica compartida de creación con rol fijo (nunca tomado del request,
     * así se evita el bug de origen: un mismo formulario ofreciendo ambos
     * roles asignables sin importar desde qué ítem del sidebar se entró).
     */
    private function storeConRolFijo(Request $request, string $rol, string $permiso): \Illuminate\Http\RedirectResponse
    {
        $actor = auth()->user();
        if (! $actor->can($permiso)) {
            abort(403, 'No tienes permiso para crear usuarios con ese rol.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento' => 'required|string|max:20|unique:users,numero_documento',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'enviar_credenciales' => 'nullable|boolean',
            'tiene_mas_roles' => 'nullable|boolean',
            'additional_roles' => 'nullable|array',
            'additional_roles.*' => 'string|exists:roles,name',
        ]);

        [$primerNombre, $segundoNombre] = $this->userCreation->splitNombre($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->userCreation->splitNombre($validated['apellido']);

        // FEAT-20260830-001: solo se aceptan roles adicionales si el
        // checkbox "tiene más roles" está marcado, y solo del universo
        // permitido (nunca se confía en lo que llegue del formulario tal cual).
        $additionalRoles = $request->boolean('tiene_mas_roles')
            ? array_values(array_intersect(
                $validated['additional_roles'] ?? [],
                \App\Support\RoleAssignmentMatrix::additionalRoleOptionNamesFor($rol)
            ))
            : [];

        // co_investigador no tiene centro de formación propio (ver
        // TrainingCenterAccess) — pero si algún rol adicional sí lo exige
        // (ej. lider_semillero), el centro del admin que crea sigue aplicando.
        $algunRolExigeCentro = $rol !== 'co_investigador'
            || collect($additionalRoles)->contains(fn (string $r) => TrainingCenterAccess::roleRequiresTrainingCenter($r));
        $trainingCenterId = $algunRolExigeCentro ? $actor->training_center_id : null;

        $plainPassword = $validated['password'];

        $user = $this->userCreation->crearUsuario([
            'email' => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
            'tipo_documento' => $validated['tipo_documento'],
            'password' => $plainPassword,
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'rol' => $rol,
            'additional_roles' => $additionalRoles,
            'created_by_user_id' => $actor->id,
        ], $trainingCenterId);

        if ($request->boolean('enviar_credenciales')) {
            $this->notificacion->enviarCredenciales($user, $plainPassword);
        }

        $nombre = trim("{$primerNombre} {$primerApellido}");
        $mensaje = $request->boolean('enviar_credenciales')
            ? "Usuario {$nombre} creado. Se enviaron las credenciales por correo."
            : "Usuario {$nombre} creado correctamente.";

        return redirect()->route('admin.usuarios.index')->with('success', $mensaje);
    }

    /**
     * Show the form for editing the specified user.
     * Permission: usuarios.editar
     */
    public function edit($id)
    {
        $this->authorize('usuarios.editar');

        $usuario = $this->findUserScoped((int) $id);
        $roles = Role::whereIn('name', $this->assignableRoles())->orderBy('name')->get();
        $trainingCenters = collect();

        $canManageAdditionalRoles = $this->canManageAdditionalRoles($usuario);
        $namesRol = $usuario->roles->pluck('name')->all();
        $rolPrincipalActual = ($usuario->primary_role_name && in_array($usuario->primary_role_name, $namesRol, true))
            ? $usuario->primary_role_name
            : (\App\Support\RoleModuleLinks::pickPrimaryRoleNameFromNames($namesRol) ?? $usuario->roles->first()?->name ?? '');
        $additionalRoleOptions = $canManageAdditionalRoles
            ? Role::whereIn('name', \App\Support\RoleAssignmentMatrix::additionalRoleOptionNamesFor($rolPrincipalActual))->orderBy('name')->get()
            : collect();
        $currentAdditionalRoles = array_values(array_diff($namesRol, [$rolPrincipalActual]));

        return view('admin.usuarios.edit', compact(
            'usuario', 'roles', 'trainingCenters',
            'canManageAdditionalRoles', 'additionalRoleOptions', 'currentAdditionalRoles',
        ));
    }

    /**
     * FEAT-20260830-001: solo super_administrador, o administrador_sistema
     * editando a OTRO usuario (nunca a sí mismo), puede gestionar roles
     * adicionales.
     */
    private function canManageAdditionalRoles(User $usuario): bool
    {
        $auth = auth()->user();
        if (TrainingCenterAccess::isSuperAdmin($auth)) {
            return true;
        }

        return $auth->id !== $usuario->id;
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
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'numero_documento' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($usuario->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($usuario->id)],
            'rol' => ['required', Rule::in($this->assignableRoles())],
            'tiene_mas_roles' => 'nullable|boolean',
            'additional_roles' => 'nullable|array',
            'additional_roles.*' => 'string|exists:roles,name',
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

        [$primerNombre, $segundoNombre] = $this->userCreation->splitNombre($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->userCreation->splitNombre($validated['apellido']);

        $userAttrs = [
            'email' => $validated['email'],
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
                'primer_nombre' => $primerNombre,
                'segundo_nombre' => $segundoNombre,
                'primer_apellido' => $primerApellido,
                'segundo_apellido' => $segundoApellido,
                'email_institucional' => $validated['email'],
            ]);
        } else {
            Person::create([
                'user_id' => $usuario->id,
                'primer_nombre' => $primerNombre,
                'segundo_nombre' => $segundoNombre,
                'primer_apellido' => $primerApellido,
                'segundo_apellido' => $segundoApellido,
                'email_institucional' => $validated['email'],
                'entity_position_id' => \App\Models\EntityPosition::first()?->id,
                'linkage_type_id' => \App\Models\LinkageType::first()?->id,
                'training_program_id' => \App\Models\TrainingProgram::first()?->id,
                'genero' => 'prefiero no decirlo',
                'celular' => 0,
                'eps' => '',
            ]);
        }

        TrainingCenterAccess::validateCentroBoundRoleAssignment($usuario, $validated['rol'], auth()->user());

        // Asegurar el rol elegido sin quitar otros roles (evitar syncRoles).
        if (! $usuario->hasRole($validated['rol'])) {
            $usuario->assignRole($validated['rol']);
        }
        $usuario->primary_role_name = $validated['rol'];
        $usuario->save();

        \App\Support\RoleAssignmentMatrix::syncAdditionalRoles(
            $usuario,
            $validated['rol'],
            $request->boolean('tiene_mas_roles') ? ($validated['additional_roles'] ?? []) : [],
            $this->canManageAdditionalRoles($usuario),
        );

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Aplica el aislamiento por centro Y la regla de edición uno-a-uno:
     * solo quien creó la cuenta (o Súper Administrador) puede gestionarla.
     */
    private function findUserScoped(int $id): User
    {
        $usuario = TrainingCenterAccess::scopeUserQueryForList(User::query(), auth()->user())
            ->findOrFail($id);

        if (! UserOwnershipAccess::canManage(auth()->user(), $usuario)) {
            abort(403, 'Solo quien creó esta cuenta puede gestionarla.');
        }

        return $usuario;
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
