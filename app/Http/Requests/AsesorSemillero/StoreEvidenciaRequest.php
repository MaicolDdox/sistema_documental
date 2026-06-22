<?php

namespace App\Http\Requests\AsesorSemillero;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'archivo'     => 'required|file|mimes:pdf,docx,jpg,jpeg,png,xlsx|max:10240',
            'nombre'      => 'required|string|max:200',
            'descripcion' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required'  => 'El archivo de evidencia es obligatorio.',
            'archivo.file'      => 'El archivo no es válido.',
            'archivo.mimes'     => 'El archivo debe ser PDF, Word, imagen (JPG, PNG) o Excel.',
            'archivo.max'       => 'El archivo no puede superar los 10 MB.',
            'nombre.required'   => 'El nombre de la evidencia es obligatorio.',
            'nombre.max'        => 'El nombre no puede superar los 200 caracteres.',
        ];
    }
}
