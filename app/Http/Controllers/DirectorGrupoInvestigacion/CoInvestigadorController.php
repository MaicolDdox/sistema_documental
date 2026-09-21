<?php

namespace App\Http\Controllers\DirectorGrupoInvestigacion;

use App\Enums\TipoDocumentoEnum;
use App\Http\Controllers\Controller;
use App\Models\GrupoInvestigacion;
use App\Models\User;
use App\Services\Admin\NotificacionService;
use App\Services\Admin\UserCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Creación de co_investigador_gdi por su director_grupo_investigacion —
 * reforma GDI/SDI. El usuario queda vinculado al grupo del director que lo
 * crea (users.grupo_investigacion_id), heredado automáticamente al crear
 * productos Minciencias (ver CoinvestigadorGdi\MincienciasProductoController).
 */
class CoInvestigadorController extends Controller
{
    public function __construct(
        private readonly UserCreationService $userCreation,
        private readonly NotificacionService $notificacion,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('usuarios.crear_co_investigador_gdi');

        $grupo = GrupoInvestigacion::where('director_id', Auth::id())->firstOrFail();

        $query = User::role('co_investigador_gdi')
            ->with('person')
            ->where('grupo_investigacion_id', $grupo->id);

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

        return view('director_grupo_investigacion.co_investigadores.index', compact('coInvestigadores'));
    }

    public function create()
    {
        $this->authorize('usuarios.crear_co_investigador_gdi');

        return view('director_grupo_investigacion.co_investigadores.create');
    }

    public function store(Request $request)
    {
        $this->authorize('usuarios.crear_co_investigador_gdi');

        $grupo = GrupoInvestigacion::where('director_id', Auth::id())->firstOrFail();

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
            'rol' => 'co_investigador_gdi',
            'created_by_user_id' => Auth::id(),
        ], Auth::user()->training_center_id);

        $user->update(['grupo_investigacion_id' => $grupo->id]);

        if ($request->boolean('enviar_credenciales')) {
            $this->notificacion->enviarCredenciales($user, $validated['password']);
        }

        return redirect()->route('director-grupo-investigacion.co-investigadores.index')
            ->with('success', 'Co-investigador GDI creado correctamente.');
    }
}
