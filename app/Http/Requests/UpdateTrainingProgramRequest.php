<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainingProgramRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'training_record_id' => ['required', 'exists:training_records,id'],
            'training_program_type_id' => ['required', 'exists:training_program_types,id'],
            'descripccion' => ['nullable', 'string', 'max:500'],
            'jornada' => ['required', 'in:diurna,nocturna,presencial'],
            'modalidad' => ['required', 'in:presencial,virtual'],
            'estado' => ['required', 'in:activo,inactivo'],
        ];
    }
}
