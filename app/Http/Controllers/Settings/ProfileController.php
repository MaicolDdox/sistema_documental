<?php

namespace App\Http\Controllers\Settings;

use App\Concerns\ProfileValidationRules;
use App\Enums\GeneroEnum;
use App\Http\Controllers\Controller;
use App\Models\EntityPosition;
use App\Models\LinkageType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use ProfileValidationRules;

    /**
     * Muestra el formulario de edición del perfil del usuario autenticado.
     */
    public function edit()
    {
        $user   = Auth::user();
        $person = $user->person;

        return view('settings.profile-page', [
            'user'             => $user,
            'person'           => $person,
            'entityPositions'  => EntityPosition::orderBy('nombre')->get(),
            'linkageTypes'     => LinkageType::orderBy('nombre')->get(),
            'generos'          => GeneroEnum::cases(),
        ]);
    }

    /**
     * Actualiza la información del perfil del usuario autenticado.
     */
    public function update(Request $request)
    {
        $user   = Auth::user();
        $person = $user->person;

        $validated = $request->validate(
            $this->profileRules($user->id, $person?->id)
        );

        $user->email = $validated['email'];
        $user->save();

        $personData = [
            'primer_nombre'       => $validated['primer_nombre'],
            'segundo_nombre'      => $validated['segundo_nombre'] ?: null,
            'primer_apellido'     => $validated['primer_apellido'],
            'segundo_apellido'    => $validated['segundo_apellido'] ?: null,
            'telefono'            => $validated['telefono'] ?: null,
            'celular'             => $validated['celular'] ?: null,
            'genero'              => $validated['genero'] ?: null,
            'eps'                 => $validated['eps'] ?: null,
            'email_institucional' => $validated['email_institucional'] ?: null,
            'entity_position_id'  => $validated['entity_position_id'] ?: null,
            'linkage_type_id'     => $validated['linkage_type_id'] ?: null,
            'cvlac_link'          => $validated['cvlac_link'] ?: null,
            // Solo se renderizan en el formulario para co_investigador; para
            // el resto de roles el campo ni siquiera llega en el request.
            'nivel_formacion'     => $validated['nivel_formacion'] ?? null,
            'fecha_vinculacion'   => $validated['fecha_vinculacion'] ?? null,
        ];

        if ($person) {
            $person->update($personData);
        } else {
            $user->person()->create($personData);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Perfil actualizado correctamente.');
    }
}
