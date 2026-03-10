<?php

namespace App\Http\Controllers\AsesorSemillero;

use App\Enums\EstadoEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\AsesorSemillero\StoreAprendizRequest;
use App\Models\EntityPosition;
use App\Models\LinkageType;
use App\Models\Person;
use App\Models\Seedling;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AprendizController extends Controller
{
    /**
     * Obtiene el semillero del asesor autenticado.
     * El asesor pertenece al semillero donde está en seedling_members.
     */
    private function getSemilleroDelAsesor(): ?Seedling
    {
        // El asesor NO está en seedling_members, sino en seedling_advisors
        // a través de su registro en external_advisors (user_id)
        return Seedling::whereHas('advisors', function ($q) {
            $q->where('external_advisors.user_id', Auth::id())
              ->where('seedling_advisors.activo', true);
        })->first();
    }

    /**
     * Permiso: aprendices.listar
     * Lista los aprendices del semillero del asesor con filtro por nombre/documento.
     */
    public function index(Request $request): View
    {
        $semillero = $this->getSemilleroDelAsesor();
        $aprendices = collect();

        if ($semillero) {
            $query = $semillero->members()
                ->with([
                    'person.trainingProgram.trainingProgramType',
                    'person.entityPosition',
                    'person.linkageType',
                ])
                ->where('users.id', '!=', Auth::id()); // no mostrar al propio asesor

            if ($request->filled('buscar')) {
                $buscar = $request->input('buscar');
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_documento', 'like', "%{$buscar}%")
                      ->orWhereHas('person', fn($p) => $p->where(
                          DB::raw("CONCAT(primer_nombre, ' ', primer_apellido)"),
                          'like',
                          "%{$buscar}%"
                      ));
                });
            }

            $aprendices = $query->paginate(15)->withQueryString();
        }

        return view('asesor_semillero.aprendices.index', compact('semillero', 'aprendices'));
    }

    /**
     * Permiso: aprendices.registrar
     * Muestra el formulario de registro de aprendiz.
     */
    public function create(): View
    {
        $cargos        = EntityPosition::all()->groupBy('descripccion');
        $tiposVinculacion = LinkageType::orderBy('nombre')->get();
        $programasFormacion = TrainingProgram::with('trainingProgramType')->orderBy('nombre')->get();

        return view('asesor_semillero.aprendices.create', compact('cargos', 'tiposVinculacion', 'programasFormacion'));
    }

    /**
     * Permiso: aprendices.registrar
     * Registra el aprendiz: crea user (inactivo) + persona + lo vincula al semillero.
     */
    public function store(StoreAprendizRequest $request): RedirectResponse
    {
        $semillero = $this->getSemilleroDelAsesor();
        if (!$semillero) {
            return redirect()->back()->with('error', 'No tienes un semillero asignado. Contacta al administrador.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $semillero) {
            // 1. Crear usuario con estado inactivo
            $user = User::create([
                'training_center_id' => Auth::user()->training_center_id,
                'email'              => $validated['email_institucional'],
                'tipo_documento'     => $validated['tipo_documento'],
                'numero_documento'   => $validated['numero_documento'],
                'password'           => Hash::make(Str::random(24)),
                'estado'             => EstadoEnum::Inactivo,
            ]);

            // 2. Crear perfil extendido en personas
            Person::create([
                'user_id'             => $user->id,
                'entity_position_id'  => $validated['entity_position_id'],
                'linkage_type_id'     => $validated['linkage_type_id'],
                'training_program_id' => $validated['training_program_id'],
                'primer_nombre'       => $validated['primer_nombre'],
                'segundo_nombre'      => $validated['segundo_nombre'] ?? null,
                'primer_apellido'     => $validated['primer_apellido'],
                'segundo_apellido'    => $validated['segundo_apellido'] ?? null,
                'genero'              => $validated['genero'],
                'celular'             => $validated['celular'],
                'telefono'            => $validated['telefono'] ?? null,
                'eps'                 => $validated['eps'],
                'email_institucional' => $validated['email_institucional'],
            ]);

            // 3. Vincular al semillero
            $semillero->members()->attach($user->id);
        });

        return redirect()->route('asesor.aprendices.index')
            ->with('success', 'Aprendiz registrado correctamente. El usuario queda sin acceso activo al sistema.');
    }

    /**
     * Permiso: aprendices.ver_detalle
     * Muestra el detalle de un aprendiz del semillero.
     */
    public function show(int $id): View
    {
        $semillero = $this->getSemilleroDelAsesor();
        $aprendiz  = $this->findAprendizEnSemillero($id, $semillero);

        return view('asesor_semillero.aprendices.show', compact('aprendiz', 'semillero'));
    }

    /**
     * Permiso: aprendices.editar
     * Muestra el formulario de edición de un aprendiz.
     */
    public function edit(int $id): View
    {
        $semillero  = $this->getSemilleroDelAsesor();
        $aprendiz   = $this->findAprendizEnSemillero($id, $semillero);
        $cargos     = EntityPosition::all()->groupBy('descripccion');
        $tiposVinculacion   = LinkageType::orderBy('nombre')->get();
        $programasFormacion = TrainingProgram::with('trainingProgramType')->orderBy('nombre')->get();

        return view('asesor_semillero.aprendices.edit', compact(
            'aprendiz', 'cargos', 'tiposVinculacion', 'programasFormacion'
        ));
    }

    /**
     * Permiso: aprendices.editar
     * Actualiza datos del aprendiz (user + persona).
     */
    public function update(StoreAprendizRequest $request, int $id): RedirectResponse
    {
        $semillero = $this->getSemilleroDelAsesor();
        $aprendiz  = $this->findAprendizEnSemillero($id, $semillero);
        $validated = $request->validated();

        DB::transaction(function () use ($aprendiz, $validated) {
            // Actualizar datos de usuario
            $aprendiz->update([
                'tipo_documento'   => $validated['tipo_documento'],
                'numero_documento' => $validated['numero_documento'],
            ]);

            // Actualizar perfil de persona
            $aprendiz->person->update([
                'entity_position_id'  => $validated['entity_position_id'],
                'linkage_type_id'     => $validated['linkage_type_id'],
                'training_program_id' => $validated['training_program_id'],
                'primer_nombre'       => $validated['primer_nombre'],
                'segundo_nombre'      => $validated['segundo_nombre'] ?? null,
                'primer_apellido'     => $validated['primer_apellido'],
                'segundo_apellido'    => $validated['segundo_apellido'] ?? null,
                'genero'              => $validated['genero'],
                'celular'             => $validated['celular'],
                'telefono'            => $validated['telefono'] ?? null,
                'eps'                 => $validated['eps'],
                'email_institucional' => $validated['email_institucional'],
            ]);
        });

        return redirect()->route('asesor.aprendices.index')
            ->with('success', 'Aprendiz actualizado correctamente.');
    }

    /**
     * Busca un aprendiz verificando que pertenezca al semillero del asesor.
     */
    private function findAprendizEnSemillero(int $userId, ?Seedling $semillero): User
    {
        if (!$semillero) {
            abort(403, 'No tienes un semillero asignado.');
        }

        $aprendiz = $semillero->members()
            ->with(['person.trainingProgram', 'person.entityPosition', 'person.linkageType'])
            ->where('users.id', $userId)
            ->firstOrFail();

        return $aprendiz;
    }
}
