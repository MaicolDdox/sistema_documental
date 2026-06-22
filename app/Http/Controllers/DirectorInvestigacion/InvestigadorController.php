<?php

namespace App\Http\Controllers\DirectorInvestigacion;

use App\Enums\EstadoEnum;
use App\Enums\RolGrupoEnum;
use App\Http\Controllers\Controller;
use App\Enums\TipoDocumentoEnum;
use App\Models\ResearchGroupUser;
use App\Models\User;
use App\Services\Admin\NotificacionService;
use App\Services\Director\InvestigadorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvestigadorController extends Controller
{
    use DirectorContext;

    public function __construct(
        private readonly InvestigadorService $service,
        private readonly NotificacionService $notificacion,
    ) {}

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
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'tipo_documento'        => ['required', Rule::enum(TipoDocumentoEnum::class)],
            'numero_documento'      => ['required', 'string', 'max:20', 'unique:users,numero_documento'],
            'primer_nombre'         => ['required', 'string', 'max:100'],
            'segundo_nombre'        => ['nullable', 'string', 'max:100'],
            'primer_apellido'       => ['required', 'string', 'max:100'],
            'segundo_apellido'      => ['nullable', 'string', 'max:100'],
            'cvlac_link'            => ['required', 'string', 'max:500'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'enviar_credenciales'   => ['nullable', 'boolean'],
        ]);

        $plainPassword = $validated['password'];
        $validated['training_center_id'] = Auth::user()->training_center_id;

        $investigador = $this->service->crearInvestigador($validated, $grupoId);
        $investigador->load('person');

        if ($request->boolean('enviar_credenciales')) {
            $this->notificacion->enviarCredenciales($investigador, $plainPassword);
        }

        $embedded = $request->boolean('embedded');

        $route = $embedded
            ? route('director.investigadores.index', ['embedded' => 1])
            : route('director.investigadores.index');

        $mensaje = $request->boolean('enviar_credenciales')
            ? 'Investigador creado exitosamente. Se enviaron las credenciales por correo.'
            : 'Investigador creado exitosamente. Recuerda entregarle las credenciales personalmente.';

        return redirect()->to($route)->with('success', $mensaje);
    }

    /**
     * Formulario de edición de los datos personales de un investigador del grupo.
     */
    public function edit(User $investigador): View
    {
        $grupoId = $this->getGrupoId();

        abort_unless(
            ResearchGroupUser::where('research_group_id', $grupoId)
                ->where('user_id', $investigador->id)
                ->whereNot('rol', RolGrupoEnum::Director)
                ->exists(),
            403,
            'Este investigador no pertenece a tu grupo.'
        );

        $investigador->load('person');

        $rolesGrupo = collect(RolGrupoEnum::cases())
            ->filter(fn($r) => $r !== RolGrupoEnum::Director)
            ->mapWithKeys(fn($r) => [$r->value => ucfirst(str_replace('_', ' ', $r->value))]);

        return view('director_investigacion.investigadores.edit', compact('investigador', 'rolesGrupo'));
    }

    /**
     * Actualiza los datos personales del investigador.
     * El director solo puede editar primer/segundo nombre/apellido y cvlac_link.
     */
    public function update(Request $request, User $investigador): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        abort_unless(
            ResearchGroupUser::where('research_group_id', $grupoId)
                ->where('user_id', $investigador->id)
                ->whereNot('rol', RolGrupoEnum::Director)
                ->exists(),
            403,
            'Este investigador no pertenece a tu grupo.'
        );

        abort_if($investigador->id === Auth::id(), 403, 'No puedes editar tu propio perfil desde este módulo.');

        $validated = $request->validate([
            'primer_nombre'    => ['required', 'string', 'max:100'],
            'segundo_nombre'   => ['nullable', 'string', 'max:100'],
            'primer_apellido'  => ['required', 'string', 'max:100'],
            'segundo_apellido' => ['nullable', 'string', 'max:100'],
            'cvlac_link'       => ['nullable', 'url', 'max:500'],
        ], [
            'primer_nombre.required'   => 'El primer nombre es obligatorio.',
            'primer_apellido.required' => 'El primer apellido es obligatorio.',
            'cvlac_link.url'           => 'El enlace CvLAC debe ser una URL válida.',
        ]);

        if ($investigador->person) {
            $investigador->person->update($validated);
        } else {
            $investigador->person()->create(array_merge($validated, [
                'genero' => 'prefiero no decirlo',
                'celular' => 0,
                'eps'    => '',
                'email_institucional' => $investigador->email,
            ]));
        }

        return redirect()
            ->route('director.investigadores.index')
            ->with('success', 'Datos del investigador actualizados correctamente.');
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
     * Permite al director resetear la contraseña de un investigador de su grupo.
     */
    public function resetPassword(Request $request, User $investigador): RedirectResponse
    {
        $grupoId = $this->getGrupoId();

        // Seguridad: el investigador debe pertenecer al grupo del director
        abort_unless(
            ResearchGroupUser::where('research_group_id', $grupoId)
                ->where('user_id', $investigador->id)
                ->whereNot('rol', RolGrupoEnum::Director)
                ->exists(),
            403
        );

        $request->validate([
            'nueva_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nueva_password.required'  => 'La nueva contraseña es obligatoria.',
            'nueva_password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'nueva_password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $investigador->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->nueva_password),
        ]);

        $director = Auth::user();
        $nombreDirector = $director->person
            ? trim($director->person->primer_nombre . ' ' . $director->person->primer_apellido)
            : $director->email;

        $this->notificacion->enviarContrasenaRestablecida($investigador, $request->nueva_password, $nombreDirector);

        return back()->with('success', "Contraseña de {$investigador->person?->primer_nombre} restablecida. Se envió una notificación al correo del investigador.");
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

