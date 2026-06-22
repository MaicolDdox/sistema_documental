<?php

declare(strict_types=1);

namespace App\Services\AsesorSemillero;

use App\Enums\EstadoEnum;
use App\Models\Project;
use App\Models\ProjectAuthor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProyectoService
{
    /**
     * Crea un proyecto, lo vincula al semillero y agrega al asesor como primer autor.
     */
    public function crearProyecto(array $validated, int $semilleroId): Project
    {
        return DB::transaction(function () use ($validated, $semilleroId) {
            $proyecto = Project::create([
                'project_creator_id'         => Auth::id(),
                'research_line_id'            => $validated['research_line_id'],
                'technological_line_id'       => $validated['technological_line_id'] ?? null,
                'thematic_area_id'            => $validated['thematic_area_id'] ?? null,
                'project_modality_id'         => $validated['project_modality_id'],
                'investigation_type_id'       => $validated['investigation_type_id'],
                'nombre'                      => $validated['nombre'],
                'descripcion'                => $validated['descripcion'] ?? null,
                'fecha_inicio'                => $validated['fecha_inicio'],
                'fecha_fin'                   => $validated['fecha_fin'] ?? null,
                'estado'                      => EstadoEnum::Activo,
                'vinculacion_macro_proyecto'  => (bool) $validated['tiene_macroproyecto'],
                'macro_project_id'            => $validated['tiene_macroproyecto'] ? ($validated['macro_project_id'] ?? null) : null,
                'tipo_financiacion'           => $validated['tipo_financiacion'] ?? null,
            ]);

            $proyecto->seedlings()->attach($semilleroId);

            ProjectAuthor::create([
                'project_id' => $proyecto->id,
                'user_id'    => Auth::id(),
                'activo'     => true,
            ]);

            return $proyecto;
        });
    }

    /**
     * Actualiza un proyecto y su vínculo con el semillero.
     */
    public function actualizarProyecto(Project $proyecto, array $validated, int $semilleroId): void
    {
        DB::transaction(function () use ($proyecto, $validated, $semilleroId) {
            $proyecto->update([
                'research_line_id'           => $validated['research_line_id'],
                'technological_line_id'      => $validated['technological_line_id'] ?? null,
                'thematic_area_id'           => $validated['thematic_area_id'] ?? null,
                'project_modality_id'        => $validated['project_modality_id'],
                'nombre'                     => $validated['nombre'],
                'descripcion'               => $validated['descripcion'] ?? null,
                'fecha_inicio'               => $validated['fecha_inicio'],
                'fecha_fin'                  => $validated['fecha_fin'] ?? null,
                'vinculacion_macro_proyecto' => (bool) $validated['tiene_macroproyecto'],
                'macro_project_id'           => $validated['tiene_macroproyecto'] ? ($validated['macro_project_id'] ?? null) : null,
                'tipo_financiacion'          => $validated['tipo_financiacion'] ?? null,
            ]);

            $proyecto->seedlings()->sync([$semilleroId]);
        });
    }
}
