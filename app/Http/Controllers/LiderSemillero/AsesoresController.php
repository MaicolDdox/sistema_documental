<?php

namespace App\Http\Controllers\LiderSemillero;

use App\Http\Controllers\Controller;
use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Models\ExternalAdvisor;
use App\Models\SeedlingAdvisor;
use App\Models\User;
use App\Support\RoleModuleLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AsesoresController extends Controller
{
    /**
     * Lista los asesores vinculados al semillero que lidera el usuario.
     * Usa la tabla pivot para mostrar estado activo/inactivo y permitir activar/desactivar.
     */
    public function index(): View
    {
        $semillero = Auth::user()->ledSeedlings()->first();

        $vinculos = $semillero
            ? $semillero->seedlingAdvisors()->with('externalAdvisor.user')->get()
            : collect();

        $asesoresDisponibles = $semillero
            ? ExternalAdvisor::query()
                ->whereDoesntHave('seedlings', function ($q) use ($semillero) {
                    $q->where('seedlings.id', $semillero->id);
                })
                ->orderBy('nombre_completo')
                ->get(['id', 'nombre_completo', 'email'])
            : collect();

        return view('lider_semillero.asesores.index', [
            'semillero' => $semillero,
            'vinculos'  => $vinculos,
            'asesoresDisponibles' => $asesoresDisponibles,
        ]);
    }

    /**
     * Crea un asesor y lo vincula al semillero (o vincula uno existente). El asesor puede estar en varios semilleros.
     * Opcionalmente crea cuenta con rol asesor_semillero.
     */
    public function store(Request $request): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero) {
            return redirect()->route('lider-sem.asesores')->with('error', 'No tienes un semillero asignado.');
        }

        // Vincular asesor existente (ya registrado) a este semillero
        if ($request->filled('external_advisor_id')) {
            $validated = $request->validate([
                'external_advisor_id' => 'required|exists:external_advisors,id',
            ]);

            $advisor = ExternalAdvisor::query()->findOrFail((int) $validated['external_advisor_id']);

            $vinculo = SeedlingAdvisor::firstOrCreate(
                [
                    'seedling_id'         => $semillero->id,
                    'external_advisor_id' => $advisor->id,
                ],
                ['activo' => true]
            );

            if (!$vinculo->wasRecentlyCreated) {
                return redirect()->route('lider-sem.asesores')
                    ->with('warning', 'Este asesor ya estaba vinculado a tu semillero.');
            }

            return redirect()->route('lider-sem.asesores')
                ->with('success', 'Asesor vinculado al semillero.');
        }

        $crearCuenta = $request->boolean('crear_cuenta');
        $rules = [
            'nombre_completo' => 'required|string|max:255',
            'email'           => $crearCuenta ? 'required|email' : 'nullable|email|max:255',
            'telefono'        => 'nullable|string|max:50',
            'institucion'     => 'nullable|string|max:255',
            'crear_cuenta'    => 'nullable|boolean',
        ];
        if ($crearCuenta) {
            $rules['numero_documento'] = 'required|numeric';
        }
        $validated = $request->validate($rules);

        $userId = null;
        $passwordTemporal = null;
        $advisor = null;
        $eraUsuarioExistente = false;

        if ($crearCuenta) {
            $existingUser = User::where('email', $validated['email'])->first();
            if ($existingUser) {
                // Ya existe usuario con ese email: reutilizar y solo vincular al semillero (puede estar en varios)
                $eraUsuarioExistente = true;
                $userId = $existingUser->id;
                if (! $existingUser->hasRole('asesor_semillero')) {
                    RoleModuleLinks::lockPrimaryRoleBeforeAddingRole($existingUser);
                    $existingUser->assignRole('asesor_semillero');
                }
                $advisor = ExternalAdvisor::firstOrCreate(
                    ['user_id' => $existingUser->id],
                    [
                        'nombre_completo' => $validated['nombre_completo'],
                        'email'           => $validated['email'],
                        'telefono'        => $validated['telefono'] ?? null,
                        'institucion'     => $validated['institucion'] ?? null,
                    ]
                );
            } else {
                // Usuario nuevo: validar documento único y crear todo
                $request->validate(['numero_documento' => 'unique:users,numero_documento']);
                $passwordTemporal = Str::random(10);
                $leader = Auth::user();
                $newUser = User::create([
                    'training_center_id' => $leader->training_center_id,
                    'email'              => $validated['email'],
                    'numero_documento'   => (int) $validated['numero_documento'],
                    'tipo_documento'     => TipoDocumentoEnum::CedulaCiudadana,
                    'password'           => Hash::make($passwordTemporal),
                    'estado'             => EstadoEnum::Activo,
                ]);
                $nombrePartes = preg_split('/\s+/', trim($validated['nombre_completo']), 2);
                $newUser->person()->create([
                    'primer_nombre'       => $nombrePartes[0] ?? $validated['nombre_completo'],
                    'primer_apellido'     => $nombrePartes[1] ?? '',
                    'segundo_apellido'    => '',
                    'email_institucional' => $validated['email'],
                    'genero'               => 'prefiero no decirlo',
                    'celular'             => 0,
                    'eps'                 => '',
                ]);
                $newUser->assignRole('asesor_semillero');
                $newUser->forceFill(['primary_role_name' => 'asesor_semillero'])->saveQuietly();
                $userId = $newUser->id;
                $advisor = ExternalAdvisor::create([
                    'user_id'         => $userId,
                    'nombre_completo' => $validated['nombre_completo'],
                    'email'           => $validated['email'],
                    'telefono'        => $validated['telefono'] ?? null,
                    'institucion'     => $validated['institucion'] ?? null,
                ]);
            }
        } else {
            // Sin cuenta: buscar asesor externo por email (puede estar en varios semilleros)
            if (!empty($validated['email'])) {
                $advisor = ExternalAdvisor::where('email', $validated['email'])->first();
            }
            if (!$advisor) {
                $advisor = ExternalAdvisor::create([
                    'user_id'         => null,
                    'nombre_completo' => $validated['nombre_completo'],
                    'email'           => $validated['email'] ?? null,
                    'telefono'        => $validated['telefono'] ?? null,
                    'institucion'     => $validated['institucion'] ?? null,
                ]);
            }
        }

        // Vincular asesor a este semillero (si ya estaba vinculado, no duplicar)
        $vinculo = SeedlingAdvisor::firstOrCreate(
            [
                'seedling_id'         => $semillero->id,
                'external_advisor_id' => $advisor->id,
            ],
            ['activo' => true]
        );

        if (!$vinculo->wasRecentlyCreated) {
            return redirect()->route('lider-sem.asesores')
                ->with('warning', 'Este asesor ya estaba vinculado a tu semillero.');
        }

        if ($crearCuenta && $passwordTemporal) {
            return redirect()->route('lider-sem.asesores')
                ->with('success', 'Asesor creado y vinculado. Se creó cuenta con rol Asesor de Semillero.')
                ->with('credenciales', [
                    'email'      => $validated['email'],
                    'password'   => $passwordTemporal,
                ]);
        }

        if ($crearCuenta && $eraUsuarioExistente) {
            return redirect()->route('lider-sem.asesores')
                ->with('success', 'Asesor vinculado al semillero. Ya tenía cuenta en el sistema (puede estar en varios semilleros).');
        }

        return redirect()->route('lider-sem.asesores')->with('success', 'Asesor vinculado al semillero.');
    }

    /**
     * Activa o desactiva un asesor del semillero (solo el líder del semillero).
     */
    public function toggle(Request $request, SeedlingAdvisor $vinculo): RedirectResponse
    {
        $this->authorize('asesores_externos.vincular_semillero');

        $semillero = Auth::user()->ledSeedlings()->first();

        if (!$semillero || $vinculo->seedling_id !== $semillero->id) {
            abort(403, 'No puedes modificar este asesor.');
        }

        $vinculo->activo = !($vinculo->activo ?? true);
        $vinculo->save();

        $estado = $vinculo->activo ? 'activado' : 'desactivado';
        return redirect()->route('lider-sem.asesores')
            ->with('success', "Asesor {$estado} correctamente.");
    }

    /**
     * Actualiza los datos del asesor externo vinculado.
     */
    public function update(Request $request, SeedlingAdvisor $vinculo): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero || $vinculo->seedling_id !== $semillero->id) {
            abort(403, 'No puedes modificar este asesor.');
        }

        $advisor = $vinculo->externalAdvisor;
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'telefono'        => 'nullable|string|max:50',
            'institucion'     => 'nullable|string|max:255',
        ]);

        $advisor->update($validated);

        return redirect()->route('lider-sem.asesores')
            ->with('success', 'Asesor actualizado correctamente.');
    }

    /**
     * Desvincula (elimina el vínculo) del asesor con el semillero. No borra el asesor externo.
     */
    public function destroy(SeedlingAdvisor $vinculo): RedirectResponse
    {
        $semillero = Auth::user()->ledSeedlings()->first();
        if (!$semillero || $vinculo->seedling_id !== $semillero->id) {
            abort(403, 'No puedes eliminar este asesor.');
        }

        $vinculo->delete();

        return redirect()->route('lider-sem.asesores')
            ->with('success', 'Asesor desvinculado del semillero correctamente.');
    }
}
