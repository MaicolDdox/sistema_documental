<?php

namespace App\Services\Investigador;

use App\Enums\EstadoEnum;
use App\Models\Project;
use App\Models\ResearchGroup;
use Illuminate\Support\Facades\DB;

class ProyectoService
{
    /**
     * Crea un nuevo proyecto, lo vincula al grupo y opcionalmente
     * registra la vinculación con un macro-proyecto.
     */
    public function crear(array $data, int $userId, int $grupoId): Project
    {
        return DB::transaction(function () use ($data, $userId, $grupoId) {
            $proyecto = Project::create([
                'project_creator_id'      => $userId,
                'research_line_id'        => $data['research_line_id'],
                'technological_line_id'   => $data['technological_line_id'] ?? null,
                'thematic_area_id'        => $data['thematic_area_id'] ?? null,
                'project_modality_id'     => $data['project_modality_id'] ?? null,
                'investigation_type_id'   => $data['investigation_type_id'] ?? null,
                'nombre'                  => $data['nombre'],
                'descripcion'            => $data['descripcion'] ?? null,
                'fecha_inicio'            => $data['fecha_inicio'] ?? null,
                'fecha_fin'               => $data['fecha_fin'] ?? null,
                'estado'                  => EstadoEnum::Activo,
                'vinculacion_macro_proyecto' => $data['vinculacion_macro_proyecto'] ?? false,
                'macro_project_id'        => !empty($data['vinculacion_macro_proyecto']) ? ($data['macro_project_id'] ?? null) : null,
            ]);

            // Vincular al grupo de investigación
            // La columna tipo_participacion es un ENUM: origen | aliado | cooperacion
            $proyecto->researchGroups()->attach($grupoId, [
                'tipo_participacion' => 'origen',
            ]);

            return $proyecto;
        });
    }

    /**
     * Actualiza un proyecto validando que el usuario sea el creador.
     */
    public function actualizar(Project $proyecto, array $data): Project
    {
        $proyecto->update([
            'research_line_id'      => $data['research_line_id'],
            'technological_line_id' => $data['technological_line_id'] ?? null,
            'thematic_area_id'      => $data['thematic_area_id'] ?? null,
            'project_modality_id'   => $data['project_modality_id'] ?? null,
            'investigation_type_id' => $data['investigation_type_id'] ?? null,
            'nombre'                => $data['nombre'],
            'descripcion'          => $data['descripcion'] ?? null,
            'fecha_inicio'          => $data['fecha_inicio'] ?? null,
            'fecha_fin'             => $data['fecha_fin'] ?? null,
            'vinculacion_macro_proyecto' => $data['vinculacion_macro_proyecto'] ?? false,
            'macro_project_id'      => !empty($data['vinculacion_macro_proyecto']) ? ($data['macro_project_id'] ?? null) : null,
        ]);

        return $proyecto->fresh();
    }

    /**
     * Verifica si un proyecto puede ser eliminado.
     * No se puede si ya tiene productos registrados.
     */
    public function puedeEliminar(Project $proyecto): bool
    {
        return ! $proyecto->products()->exists();
    }
}
