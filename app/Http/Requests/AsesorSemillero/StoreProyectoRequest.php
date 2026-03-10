<?php

namespace App\Http\Requests\AsesorSemillero;

use Illuminate\Foundation\Http\FormRequest;

class StoreProyectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'                 => 'required|string|max:255',
            'descripccion'           => 'nullable|string',
            'research_line_id'       => 'required|exists:research_lines,id',
            'technological_line_id'  => 'nullable|exists:technological_lines,id',
            'thematic_area_id'       => 'nullable|exists:thematic_areas,id',
            'project_modality_id'    => 'required|exists:project_modalities,id',
            'investigation_type_id'  => 'required|exists:investigation_types,id',
            'fecha_inicio'           => 'required|date',
            'fecha_fin'              => 'nullable|date|after_or_equal:fecha_inicio',
            'tiene_macroproyecto'    => 'required|boolean',
            'codigo_macro'           => 'required_if:tiene_macroproyecto,1|nullable|string|max:100',
            'nombre_macro'           => 'required_if:tiene_macroproyecto,1|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'              => 'El nombre del proyecto es obligatorio.',
            'research_line_id.required'    => 'La línea de investigación es obligatoria.',
            'research_line_id.exists'      => 'La línea de investigación no es válida.',
            'project_modality_id.required' => 'La modalidad del proyecto es obligatoria.',
            'investigation_type_id.required' => 'El tipo de investigación es obligatorio.',
            'fecha_inicio.required'        => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal'     => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'tiene_macroproyecto.required' => 'Debe indicar si el proyecto tiene macroproyecto.',
            'codigo_macro.required_if'     => 'El código del macroproyecto es obligatorio cuando el proyecto está vinculado a uno.',
            'nombre_macro.required_if'     => 'El nombre del macroproyecto es obligatorio cuando el proyecto está vinculado a uno.',
        ];
    }
}
