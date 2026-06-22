<?php

namespace App\Http\Requests\AsesorSemillero;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'                 => 'required|exists:projects,id',
            'nombre'                     => 'required|string|max:255',
            'tiene_repositorio'          => 'nullable|boolean',
            'url_repositorio'            => 'nullable|url|max:500',
            'archivo'                    => 'nullable|file|mimes:pdf,docx,xlsx,pptx|max:20480',
            'autores'                    => 'nullable|array',
            'autores.*'                  => 'integer|exists:project_authors,id',
            // Campos opcionales — se rellenan con defaults en el controller
            'minciencias_typology_id'    => 'nullable|exists:minciencias_typologies,id',
            'minciencias_subcategory_id' => 'nullable|exists:minciencias_subcategories,id',
            'knowledge_area_id'          => 'nullable|exists:knowledge_areas,id',
            'anio_publicacion'           => 'nullable|integer|min:2000|max:' . date('Y'),
            'descripcion'               => 'nullable|string',
        ];
    }

    /**
     * Exige que el usuario proporcione al menos: un archivo cargado O una URL de enlace.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $tieneArchivo = $this->hasFile('archivo');
            $tieneUrl     = filled($this->input('url_repositorio'));

            if (!$tieneArchivo && !$tieneUrl) {
                $v->errors()->add(
                    'archivo',
                    'Debes adjuntar un archivo o ingresar un enlace externo (Google Drive, OneDrive, GitHub, etc.)'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'project_id.required'      => 'Debes seleccionar un proyecto.',
            'project_id.exists'        => 'El proyecto seleccionado no es válido.',
            'nombre.required'          => 'El nombre o título del producto es obligatorio.',
            'url_repositorio.required' => 'La URL del repositorio es obligatoria.',
            'url_repositorio.url'      => 'Debe ser una URL válida (ej. https://...).',
            'archivo.required'         => 'Debes adjuntar el archivo del producto.',
            'archivo.mimes'            => 'El archivo debe ser PDF, Word, Excel o PowerPoint.',
            'archivo.max'              => 'El archivo no puede superar los 20 MB.',
        ];
    }
}
