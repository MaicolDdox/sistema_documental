<?php

declare(strict_types=1);

namespace App\Services\LiderSemillero;

use App\Enums\EstadoEnum;
use App\Models\Project;
use App\Models\Seedling;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Creación/edición de proyectos por el Líder de Semillero (rediseño de roles).
 * El proyecto pertenece a UN semillero y tiene UN Líder de Proyecto asignado.
 */
class ProyectoLiderService
{
    public function crearProyecto(array $validated, Seedling $semillero): Project
    {
        return DB::transaction(function () use ($validated, $semillero) {
            $proyecto = Project::create([
                'project_creator_id' => Auth::id(),
                'seedling_id' => $semillero->id,
                'lider_proyecto_user_id' => $validated['lider_proyecto_user_id'],
                'research_line_id' => $validated['research_line_id'],
                'technological_line_id' => $validated['technological_line_id'] ?? null,
                'thematic_area_id' => $validated['thematic_area_id'] ?? null,
                'project_modality_id' => $validated['project_modality_id'] ?? null,
                'investigation_type_id' => $validated['investigation_type_id'] ?? null,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'] ?? null,
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'estado' => EstadoEnum::Activo,
                'vinculacion_macro_proyecto' => false,
                'tipo_financiacion' => $validated['tipo_financiacion'] ?? null,
                'tipo_proyecto_origen' => $validated['tipo_proyecto_origen'] ?? null,
            ]);

            return $proyecto;
        });
    }

    public function actualizarProyecto(Project $proyecto, array $validated): void
    {
        $proyecto->update([
            'lider_proyecto_user_id' => $validated['lider_proyecto_user_id'],
            'research_line_id' => $validated['research_line_id'],
            'technological_line_id' => $validated['technological_line_id'] ?? null,
            'thematic_area_id' => $validated['thematic_area_id'] ?? null,
            'project_modality_id' => $validated['project_modality_id'] ?? null,
            'investigation_type_id' => $validated['investigation_type_id'] ?? null,
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'fecha_inicio' => $validated['fecha_inicio'] ?? null,
            'fecha_fin' => $validated['fecha_fin'] ?? null,
            'tipo_financiacion' => $validated['tipo_financiacion'] ?? null,
            'tipo_proyecto_origen' => $validated['tipo_proyecto_origen'] ?? null,
        ]);
    }
}
