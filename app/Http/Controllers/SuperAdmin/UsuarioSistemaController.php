<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Http\Controllers\Controller;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Services\Admin\NotificacionService;
use App\Services\Admin\ResearchGroupService;
use App\Services\Admin\UserCreationService;
use App\Support\TrainingCenterAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioSistemaController extends Controller
{
    // ─────────────────────────────────────────────────
    // Roles exclusivos del super administrador — nunca
    // deben aparecer ni crearse desde este módulo
    // ─────────────────────────────────────────────────
    private const ROLES_EXCLUIDOS = ['administrador_sistema', 'super_administrador'];

    public function __construct(
        private readonly UserCreationService $userCreation,
        private readonly NotificacionService $notificacion,
        private readonly ResearchGroupService $researchGroup,
    ) {}

    // ─────────────────────────────────────────────────
    // Listado
    // ─────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = User::with(['person', 'trainingCenter', 'roles'])
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', self::ROLES_EXCLUIDOS));

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

        if ($request->filled('rol')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->rol));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $usuarios = $query->latest()->paginate(15)->withQueryString();
        $roles    = Role::whereNotIn('name', self::ROLES_EXCLUIDOS)->orderBy('name')->get();
        $estados  = EstadoEnum::cases();

        return view('super-admin.usuarios-sistema.index', compact('usuarios', 'roles', 'estados'));
    }

    // ─────────────────────────────────────────────────
    // Crear
    // ─────────────────────────────────────────────────

    public function create(): View
    {
        $roles   = Role::whereNotIn('name', self::ROLES_EXCLUIDOS)->orderBy('name')->get();
        $centros = TrainingCenter::activos()->orderBy('nombre')->get();

        return view('super-admin.usuarios-sistema.create', compact('roles', 'centros'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'              => 'required|string|max:100',
            'apellido'            => 'required|string|max:100',
            'tipo_documento'      => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento'    => 'required|string|max:20|unique:users,numero_documento',
            'email'               => 'required|email|unique:users,email',
            'password'            => 'required|string|min:8|confirmed',
            'rol'                 => ['nullable', 'exists:roles,name', Rule::notIn(self::ROLES_EXCLUIDOS)],
            'training_center_id'  => 'nullable|exists:training_centers,id',
            'enviar_credenciales' => 'nullable|boolean',
        ]);

        $rol  = $validated['rol'] ?? null;
        $tcId = ($validated['training_center_id'] ?? null) ?: null;

        if (TrainingCenterAccess::roleRequiresTrainingCenter($rol) && $tcId === null) {
            return redirect()->back()
                ->withErrors(['training_center_id' => 'Este rol exige un centro de formación.'])
                ->withInput();
        }

        [$primerNombre, $segundoNombre]     = $this->userCreation->splitNombre($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->userCreation->splitNombre($validated['apellido']);

        $plainPassword = $validated['password'];

        $user = $this->userCreation->crearUsuario([
            'email'            => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
            'tipo_documento'   => $validated['tipo_documento'],
            'password'         => $plainPassword,
            'primer_nombre'    => $primerNombre,
            'segundo_nombre'   => $segundoNombre,
            'primer_apellido'  => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'rol'              => $rol,
        ], $tcId);

        if ($rol) {
            $this->researchGroup->autoVincularUsuario($user, $rol);
        }

        if ($request->boolean('enviar_credenciales')) {
            $this->notificacion->enviarCredenciales($user, $plainPassword);
        }

        $nombre  = trim("{$primerNombre} {$primerApellido}");
        $mensaje = $request->boolean('enviar_credenciales')
            ? "Usuario {$nombre} creado. Se enviaron las credenciales por correo."
            : "Usuario {$nombre} creado correctamente.";

        return redirect()->route('super-admin.usuarios-sistema.index')->with('success', $mensaje);
    }

    // ─────────────────────────────────────────────────
    // Toggle estado
    // ─────────────────────────────────────────────────

    public function toggleEstado(int $id): RedirectResponse
    {
        $usuario = User::whereDoesntHave('roles', fn ($q) => $q->whereIn('name', self::ROLES_EXCLUIDOS))
            ->findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->estado = $usuario->estado === EstadoEnum::Activo
            ? EstadoEnum::Inactivo
            : EstadoEnum::Activo;

        $usuario->save();

        return back()->with('success', 'Estado del usuario actualizado.');
    }
}
