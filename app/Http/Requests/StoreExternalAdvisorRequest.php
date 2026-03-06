<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExternalAdvisorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Convertir campos vacíos a null para que coincidan con columnas nullable en la BD.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->filled('email') ? $this->email : null,
            'telefono' => $this->filled('telefono') ? $this->telefono : null,
            'institucion' => $this->filled('institucion') ? $this->institucion : null,
            'user_id' => $this->filled('user_id') ? $this->user_id : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre_completo' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'institucion' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
