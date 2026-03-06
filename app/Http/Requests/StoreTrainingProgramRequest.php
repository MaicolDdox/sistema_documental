<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingProgramRequest extends FormRequest
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
            'ficha' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'max:255'],
            'descripccion' => ['nullable', 'string', 'max:500'],
            'jornada' => ['required', 'in:diurna,nocturna,presencial'],
            'modalidad' => ['required', 'in:presencial,virtual'],
            'estado' => ['required', 'in:activo,inactivo'],
        ];
    }
}
