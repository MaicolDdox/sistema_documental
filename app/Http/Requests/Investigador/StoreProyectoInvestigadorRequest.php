<?php

namespace App\Http\Requests\Investigador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProyectoInvestigadorRequest extends FormRequest
{
    /**
     * Determina si el usuario est autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('investigador_asociado');
    }

    /**
     * Reglas de validacin a aplicar.
     */
    public function rules(): array
    {
        return [
            'nombre'                     => ['required', 'string', 'max:255'],
            'descripcion'                => ['nullable', 'string'],
            'research_line_id'           => ['required', 'exists:research_lines,id'],
            'technological_line_id'      => ['nullable', 'exists:technological_lines,id'],
            'thematic_area_id'           => ['nullable', 'exists:thematic_areas,id'],
            'project_modality_id'        => ['nullable', 'exists:project_modalities,id'],
            'investigation_type_id'      => ['nullable', 'exists:investigation_types,id'],
            'fecha_inicio'               => ['nullable', 'date'],
            'fecha_fin'                  => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'vinculacion_macro_proyecto' => ['boolean'],
            'macro_project_id'           => ['nullable', 'exists:macro_projects,id'],
            'tipo_financiacion'          => ['nullable', 'string', 'max:100'],
        ];
    }
}
