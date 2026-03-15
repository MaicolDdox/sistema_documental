<?php

namespace App\Http\Controllers\DirectorInvestigacion;

use App\Enums\EstadoEnum;
use App\Enums\RolGrupoEnum;
use App\Http\Controllers\Controller;
use App\Models\ResearchGroupUser;
use App\Models\User;
use App\Services\Director\InvestigadorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvestigadorController extends Controller
{
    use DirectorContext;

    public function __construct(private InvestigadorService $service) {}

    /**
     * Lista todos los investigadores vinculados al grupo del director.
     */
    public function index(): View
    {
        $grupoId = $this->getGrupoId();

        $investigadores = ResearchGroupUser::with(['user.person'])
            ->where('research_group_id', $grupoId)
            ->whereNot('rol', RolGrupoEnum::Director)
            ->get();

        return view('director_investigacion.investigadores.index', compact('investigadores'));
    }

    /**
     * Formulario para crear un nuevo investigador.
     */
    public function create(): View
    {
        $rolesGrupo = collect(RolGrupoEnum::cases())
            ->filter(fn($r) => $r !== RolGrupoEnum::Director)
            ->mapWithKeys(fn($r) => [$r->value => ucfirst(str_replace('_', ' ', $r->value))]);

        return view('director_investigacion.investigadores.create', compact('rolesGrupo'));
    }

    /**
     * Almacena el nuevo investigador y lo vincula al grupo.
     */
    public function store(Request $request): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        $validated = $request->validate([
            'email'            => ['required', 'email', 'max:255', 'unique:users,email'],
            'tipo_documento'   => ['required', 'string', Rule::in(['cedula ciudadana', 'tarjeta identidad', 'cedula extranjeria', 'pasaporte'])],
            'numero_documento' => ['required', 'string', 'max:20', 'unique:users,numero_documento'],
            'primer_nombre'    => ['required', 'string', 'max:100'],
            'segundo_nombre'   => ['nullable', 'string', 'max:100'],
            'primer_apellido'  => ['required', 'string', 'max:100'],
            'segundo_apellido' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['training_center_id'] = Auth::user()->training_center_id;

        $this->service->crearInvestigador($validated, $grupoId);

        return redirect()
            ->route('director.investigadores.index')
            ->with('success', 'Investigador creado y notificado por correo exitosamente.');
    }

    /**
     * Activa o desactiva el estado del investigador.
     */
    public function toggleEstado(User $investigador): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        // Autorizar: el investigador debe pertenecer al grupo
        abort_unless(
            ResearchGroupUser::where('research_group_id', $grupoId)
                ->where('user_id', $investigador->id)
                ->whereNot('rol', RolGrupoEnum::Director)
                ->exists(),
            403
        );

        // El director no puede desactivarse a sí mismo desde aquí
        abort_if($investigador->id === Auth::id(), 403, 'No puedes modificar tu propio estado desde este módulo.');

        $nuevoEstado = $investigador->estado === EstadoEnum::Activo
            ? EstadoEnum::Inactivo
            : EstadoEnum::Activo;

        $investigador->update(['estado' => $nuevoEstado]);

        $label = $nuevoEstado === EstadoEnum::Activo ? 'activado' : 'desactivado';
        return back()->with('success', "Investigador {$label} exitosamente.");
    }

    /**
     * Cambia el rol interno del investigador dentro del grupo.
     */
    public function cambiarRol(Request $request, User $investigador): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        $request->validate([
            'rol' => ['required', Rule::enum(RolGrupoEnum::class), Rule::notIn([RolGrupoEnum::Director->value])],
        ]);

        abort_if($investigador->id === Auth::id(), 403, 'No puedes cambiar tu propio rol desde este módulo.');

        $this->service->cambiarRol($investigador->id, $grupoId, RolGrupoEnum::from($request->rol));

        return back()->with('success', 'Rol de investigador actualizado.');
    }

    /**
     * Desvincula al investigador del grupo (no lo elimina del sistema).
     */
    public function desvincular(User $investigador): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        abort_if($investigador->id === Auth::id(), 403, 'No puedes desvincularte a ti mismo.');

        $this->service->desvincular($investigador->id, $grupoId);

        return redirect()
            ->route('director.investigadores.index')
            ->with('success', 'Investigador desvinculado del grupo.');
    }
}
