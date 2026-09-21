<?php

namespace App\Http\Controllers\DirectorSemilleros;

use App\Enums\TipoDocumentoEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\NotificacionService;
use App\Services\Admin\UserCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Creación de co_investigador_sdi por director_semilleros — reforma GDI/SDI.
 * No se vincula a un Seedling directamente aquí: la vinculación real al
 * proyecto la sigue haciendo lider_proyecto vía project_authors (sin cambios
 * en ese flujo).
 */
class CoInvestigadorController extends Controller
{
    public function __construct(
        private readonly UserCreationService $userCreation,
        private readonly NotificacionService $notificacion,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('usuarios.crear_co_investigador_sdi');

        $user = Auth::user();

        $query = User::role('co_investigador_sdi')
            ->with('person')
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

        $coInvestigadores = $query->orderBy('email')->paginate(10)->withQueryString();

        return view('director_semilleros.co_investigadores.index', compact('coInvestigadores'));
    }

    public function create()
    {
        $this->authorize('usuarios.crear_co_investigador_sdi');

        return view('director_semilleros.co_investigadores.create');
    }

    public function store(Request $request)
    {
        $this->authorize('usuarios.crear_co_investigador_sdi');

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento' => 'required|string|max:20|unique:users,numero_documento',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'enviar_credenciales' => 'nullable|boolean',
        ]);

        $user = $this->userCreation->crearUsuario([
            'email' => $validated['email'],
            'numero_documento' => $validated['numero_documento'],
            'tipo_documento' => $validated['tipo_documento'],
            'password' => $validated['password'],
            'primer_nombre' => $validated['nombre'],
            'primer_apellido' => $validated['apellido'],
            'rol' => 'co_investigador_sdi',
            'created_by_user_id' => Auth::id(),
        ], Auth::user()->training_center_id);

        if ($request->boolean('enviar_credenciales')) {
            $this->notificacion->enviarCredenciales($user, $validated['password']);
        }

        return redirect()->route('dir-sem.co-investigadores.index')
            ->with('success', 'Co-investigador SDI creado correctamente.');
    }
}
