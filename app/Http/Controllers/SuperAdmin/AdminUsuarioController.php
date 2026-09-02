<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Http\Controllers\Controller;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Services\Admin\NotificacionService;
use App\Services\Admin\UserCreationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUsuarioController extends Controller
{
    private const ROL = 'administrador_sistema';

    public function __construct(
        private readonly UserCreationService $userCreation,
        private readonly NotificacionService $notificacion,
    ) {}

    public function index(Request $request): View
    {
        $query = User::with(['person', 'trainingCenter'])
            ->whereHas('roles', fn ($q) => $q->where('name', self::ROL));

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('email', 'like', "%{$term}%")
                    ->orWhere('numero_documento', 'like', "%{$term}%")
                    ->orWhereHas('person', fn ($q2) => $q2
                        ->where('primer_nombre', 'like', "%{$term}%")
                        ->orWhere('primer_apellido', 'like', "%{$term}%"));
            });
        }

        $usuarios = $query->latest()->paginate(15)->withQueryString();
        $estados = EstadoEnum::cases();

        return view('super-admin.usuarios.index', compact('usuarios', 'estados'));
    }

    public function create(): View
    {
        $centros = TrainingCenter::activos()->orderBy('nombre')->get();
        $rolesAdicionales = \Spatie\Permission\Models\Role::whereIn(
            'name',
            \App\Support\RoleAssignmentMatrix::additionalRoleOptionNamesFor(self::ROL)
        )->orderBy('name')->get();

        return view('super-admin.usuarios.create', compact('centros', 'rolesAdicionales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento' => 'required|string|max:20|unique:users,numero_documento',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'training_center_id' => 'nullable|exists:training_centers,id',
            'enviar_credenciales' => 'nullable|boolean',
            'tiene_mas_roles' => 'nullable|boolean',
            'additional_roles' => 'nullable|array',
            'additional_roles.*' => 'string|exists:roles,name',
        ]);

        [$primerNombre, $segundoNombre] = $this->userCreation->splitNombre($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->userCreation->splitNombre($validated['apellido']);

        $tcId = ($validated['training_center_id'] ?? null) ?: null;
        $plainPassword = $validated['password'];

        // FEAT-20260830-001: solo se aceptan roles adicionales si el
        // checkbox "tiene más roles" está marcado, y solo del universo
        // permitido (nunca se confía en lo que llegue del formulario tal cual).
        $additionalRoles = $request->boolean('tiene_mas_roles')
            ? array_values(array_intersect(
                $validated['additional_roles'] ?? [],
                \App\Support\RoleAssignmentMatrix::additionalRoleOptionNamesFor(self::ROL)
            ))
            : [];

        $user = $this->userCreation->crearUsuario([
            'email' => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
            'tipo_documento' => $validated['tipo_documento'],
            'password' => $plainPassword,
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'rol' => self::ROL,
            'additional_roles' => $additionalRoles,
            'created_by_user_id' => auth()->id(),
        ], $tcId);

        if ($request->boolean('enviar_credenciales')) {
            $this->notificacion->enviarCredenciales($user, $plainPassword);
        }

        $nombre = trim("{$primerNombre} {$primerApellido}");
        $mensaje = $request->boolean('enviar_credenciales')
            ? "Administrador {$nombre} creado. Se enviaron las credenciales por correo."
            : "Administrador {$nombre} creado correctamente.";

        return redirect()->route('super-admin.administradores.index')->with('success', $mensaje);
    }

    public function edit(int $id): View
    {
        $usuario = User::whereHas('roles', fn ($q) => $q->where('name', self::ROL))->findOrFail($id);
        $centros = TrainingCenter::activos()->orderBy('nombre')->get();

        $currentAdditionalRoles = array_values(array_diff($usuario->roles->pluck('name')->all(), [self::ROL]));
        $rolesAdicionales = \Spatie\Permission\Models\Role::whereIn(
            'name',
            \App\Support\RoleAssignmentMatrix::additionalRoleOptionNamesFor(self::ROL)
        )->orderBy('name')->get();

        return view('super-admin.usuarios.edit', compact('usuario', 'centros', 'rolesAdicionales', 'currentAdditionalRoles'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $usuario = User::whereHas('roles', fn ($q) => $q->where('name', self::ROL))->findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento' => ['required', 'string', 'max:20', Rule::unique('users', 'numero_documento')->ignore($usuario->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($usuario->id)],
            'training_center_id' => 'nullable|exists:training_centers,id',
            'tiene_mas_roles' => 'nullable|boolean',
            'additional_roles' => 'nullable|array',
            'additional_roles.*' => 'string|exists:roles,name',
        ]);

        $newTcId = ($validated['training_center_id'] ?? null) ?: null;
        if ($newTcId !== null && \App\Support\SystemAdminCenterLink::trainingCenterHasSystemAdmin($newTcId, $usuario->id)) {
            return redirect()->back()
                ->withErrors(['training_center_id' => 'Cada centro solo puede tener un administrador del sistema. Este centro ya está vinculado a otro usuario.'])
                ->withInput();
        }

        [$primerNombre, $segundoNombre] = $this->userCreation->splitNombre($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->userCreation->splitNombre($validated['apellido']);

        $usuario->update([
            'email' => $validated['email'],
            'tipo_documento' => $validated['tipo_documento'],
            'numero_documento' => $validated['numero_documento'],
            'training_center_id' => $newTcId,
        ]);

        if ($usuario->person) {
            $usuario->person->update([
                'primer_nombre' => $primerNombre,
                'segundo_nombre' => $segundoNombre,
                'primer_apellido' => $primerApellido,
                'segundo_apellido' => $segundoApellido,
                'email_institucional' => $validated['email'],
            ]);
        }

        \App\Support\RoleAssignmentMatrix::syncAdditionalRoles(
            $usuario,
            self::ROL,
            $request->boolean('tiene_mas_roles') ? ($validated['additional_roles'] ?? []) : [],
            true, // super_administrador siempre puede gestionar roles adicionales
        );

        return redirect()->route('super-admin.administradores.index')->with('success', 'Administrador actualizado correctamente.');
    }

    public function toggleEstado(int $id): RedirectResponse
    {
        $usuario = User::whereHas('roles', fn ($q) => $q->where('name', self::ROL))
            ->findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->estado = $usuario->estado === EstadoEnum::Activo
            ? EstadoEnum::Inactivo
            : EstadoEnum::Activo;

        $usuario->save();

        return back()->with('success', 'Estado del administrador actualizado.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $usuario = User::whereHas('roles', fn ($q) => $q->where('name', self::ROL))
            ->findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->syncRoles([]);
        $usuario->person?->delete();
        $usuario->delete();

        return redirect()->route('super-admin.administradores.index')->with('success', 'Administrador eliminado correctamente.');
    }
}
