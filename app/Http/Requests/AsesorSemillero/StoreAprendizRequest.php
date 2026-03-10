<?php

namespace App\Http\Requests\AsesorSemillero;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAprendizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // En edición ($this->id) se ignora el aprendiz actual en unique
        $userId   = $this->route('id');
        $personId = $userId
            ? \App\Models\Person::where('user_id', $userId)->value('id')
            : null;

        return [
            'primer_nombre'        => 'required|string|max:100',
            'segundo_nombre'       => 'nullable|string|max:100',
            'primer_apellido'      => 'required|string|max:100',
            'segundo_apellido'     => 'nullable|string|max:100',
            'tipo_documento'       => 'required|in:documento identidad,cedula ciudadana,pasaporte,cedula extrangera',
            'numero_documento'     => [
                'required',
                'integer',
                Rule::unique('users', 'numero_documento')->ignore($userId),
            ],
            'genero'               => 'required|in:masculino,femenino,prefiero no decirlo',
            'celular'              => 'required|numeric|digits_between:7,10',
            'telefono'             => 'nullable|numeric|digits_between:7,10',
            'eps'                  => 'required|string|max:100',
            'email_institucional'  => [
                'required',
                'email',
                Rule::unique('people', 'email_institucional')->ignore($personId),
            ],
            'entity_position_id'   => 'required|exists:entity_positions,id',
            'linkage_type_id'      => 'required|exists:linkage_types,id',
            'training_program_id'  => 'required|exists:training_programs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'primer_nombre.required'       => 'El primer nombre es obligatorio.',
            'primer_apellido.required'     => 'El primer apellido es obligatorio.',
            'tipo_documento.required'      => 'El tipo de documento es obligatorio.',
            'tipo_documento.in'            => 'Tipo de documento no válido.',
            'numero_documento.required'    => 'El número de documento es obligatorio.',
            'numero_documento.unique'      => 'Ya existe un aprendiz registrado con ese número de documento.',
            'genero.required'              => 'El género es obligatorio.',
            'celular.required'             => 'El número de celular es obligatorio.',
            'eps.required'                 => 'La EPS es obligatoria.',
            'email_institucional.required' => 'El correo institucional es obligatorio.',
            'email_institucional.unique'   => 'Ese correo institucional ya está registrado.',
            'entity_position_id.required'  => 'El cargo/rol es obligatorio.',
            'entity_position_id.exists'    => 'El cargo seleccionado no es válido.',
            'linkage_type_id.required'     => 'El tipo de vinculación es obligatorio.',
            'linkage_type_id.exists'       => 'El tipo de vinculación seleccionado no es válido.',
            'training_program_id.required' => 'El programa de formación es obligatorio.',
            'training_program_id.exists'   => 'El programa de formación seleccionado no es válido.',
        ];
    }
}
