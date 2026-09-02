<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\NotificacionService;
use App\Services\Admin\UserCreationService;
use App\Support\UserOwnershipAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestión de Líderes de Proyecto por el Líder de Semillero (matriz de
 * creación exclusiva del rediseño de roles). La asignación de un Líder de
 * Proyecto a un proyecto concreto se construye junto con el modelo de datos
 * de proyectos en una fase posterior — este controlador solo cubre la
 * creación de la cuenta.
 */
class LiderProyectoController extends Controller
{
    public function __construct(
        private readonly UserCreationService $userCreation,
        private readonly NotificacionService $notificacion,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('usuarios.crear_lider_proyecto');

        $query = User::with('person')
            ->role('lider_proyecto')
            ->where('created_by_user_id', Auth::id());

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('email', 'like', "%{$term}%")
                    ->orWhere('numero_documento', 'like', "%{$term}%")
                    ->orWhereHas('person', fn ($p) => $p
                        ->where('primer_nombre', 'like', "%{$term}%")
                        ->orWhere('primer_apellido', 'like', "%{$term}%"));
            });
        }

        $lideresProyecto = $query->orderBy('email')->paginate(15)->withQueryString();

        return view('lider_semillero.lider-proyecto.index', compact('lideresProyecto'));
    }

    public function create(): View
    {
        $this->authorize('usuarios.crear_lider_proyecto');

        return view('lider_semillero.lider-proyecto.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('usuarios.crear_lider_proyecto');

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento' => 'required|string|max:20|unique:users,numero_documento',
            'email' => 'required|email|unique:users,email',
            'enviar_credenciales' => 'nullable|boolean',
        ]);

        $password = Str::random(10);
        $actor = Auth::user();

        $newUser = $this->userCreation->crearUsuario([
            'email' => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
            'tipo_documento' => $validated['tipo_documento'],
            'password' => $password,
            'primer_nombre' => $validated['nombre'],
            'primer_apellido' => $validated['apellido'],
            'rol' => 'lider_proyecto',
            'created_by_user_id' => $actor->id,
        ], $actor->training_center_id);

        if ($request->boolean('enviar_credenciales')) {
            $this->notificacion->enviarCredenciales($newUser, $password);
        }

        return redirect()->route('lider-sem.lider-proyecto.index')
            ->with('success', 'Líder de proyecto creado exitosamente.')
            ->with('credenciales', [
                'email' => $newUser->email,
                'password' => $password,
            ]);
    }

    public function toggleEstado(User $liderProyecto): RedirectResponse
    {
        $this->authorize('usuarios.crear_lider_proyecto');
        $this->ensureOwnedLiderProyecto($liderProyecto);

        $liderProyecto->estado = $liderProyecto->estado === EstadoEnum::Activo
            ? EstadoEnum::Inactivo
            : EstadoEnum::Activo;
        $liderProyecto->save();

        return redirect()->route('lider-sem.lider-proyecto.index')->with('success', 'Estado actualizado.');
    }

    private function ensureOwnedLiderProyecto(User $liderProyecto): void
    {
        if (! $liderProyecto->hasRole('lider_proyecto')
            || ! UserOwnershipAccess::canManage(Auth::user(), $liderProyecto)) {
            abort(403, 'No tienes permiso para gestionar este usuario.');
        }
    }
}
