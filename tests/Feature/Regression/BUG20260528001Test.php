<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-001
 * La columna 'descripccion' (typo) en la tabla projects causaba
 * SQLSTATE[42S22]: Column not found al crear/actualizar proyectos.
 * Corregido: 2026-05-28 — migración rename_descripccion_to_descripcion
 */
class BUG20260528001Test extends TestCase
{
    use RefreshDatabase;

    public function test_projects_table_has_descripcion_column_without_typo(): void
    {
        $this->assertTrue(
            Schema::hasColumn('projects', 'descripcion'),
            "La columna 'descripcion' debe existir en la tabla projects"
        );

        $this->assertFalse(
            Schema::hasColumn('projects', 'descripccion'),
            "La columna con typo 'descripccion' no debe existir"
        );
    }

    public function test_project_can_be_created_with_descripcion(): void
    {
        $user = \App\Models\User::factory()->create();

        $depto = \App\Models\Department::create(['nombre' => 'Depto BUG-528']);
        $ciudad = \App\Models\City::create(['nombre' => 'Ciudad BUG-528', 'department_id' => $depto->id]);
        $centro = \App\Models\TrainingCenter::create([
            'nombre' => 'Centro BUG-528', 'codigo' => 'B528', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $researchLine = \App\Models\ResearchLine::first()
            ?? \App\Models\ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);

        $project = Project::create([
            'project_creator_id' => $user->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto de regresión BUG-20260528-001',
            'descripcion' => 'Descripción de prueba para verificar el fix del typo',
            'fecha_inicio' => now(),
            'estado' => EstadoEnum::Activo,
            'vinculacion_macro_proyecto' => false,
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'descripcion' => 'Descripción de prueba para verificar el fix del typo',
        ]);
    }
}
