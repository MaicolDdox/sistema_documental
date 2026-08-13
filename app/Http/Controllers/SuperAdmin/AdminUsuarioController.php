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

        return view('super-admin.usuarios.create', compact('centros'));
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
        ]);

        [$primerNombre, $segundoNombre] = $this->userCreation->splitNombre($validated['nombre']);
        [$primerApellido, $segundoApellido] = $this->userCreation->splitNombre($validated['apellido']);

        $tcId = ($validated['training_center_id'] ?? null) ?: null;
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
            'rol' => self::ROL,
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
