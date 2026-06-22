<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\Project;
use App\Models\Seedling;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión: BUG-20260609-004
 * DB::table('project_seedlings')->where('seedling_id', $x)->pluck('project_id')
 * aparecía repetido 8+ veces directamente en controladores (sin usar el modelo).
 * Corregido: 2026-06-09 — Seedling::projectIds() centraliza la consulta
 * usando la relación Eloquent existente projects() BelongsToMany.
 */
class BUG20260609004Test extends TestCase
{
    use RefreshDatabase;

    private function crearSemilleroConProyecto(): array
    {
        $creador = User::factory()->create();

        $semillero = Seedling::create([
            'research_group_id' => null,
            'creator_id'        => $creador->id,
            'nombre'            => 'Semillero Test',
            'codigo'            => 1001,
            'logo'              => 'default.png',
            'estado'            => EstadoEnum::Activo,
        ]);

        $researchLine = \App\Models\ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);

        $proyecto = Project::create([
            'project_creator_id' => $creador->id,
            'research_line_id'   => $researchLine->id,
            'nombre'             => 'Proyecto Test',
            'estado'             => EstadoEnum::Activo,
            'fecha_inicio'       => now(),
        ]);

        DB::table('project_seedlings')->insert([
            'seedling_id' => $semillero->id,
            'project_id'  => $proyecto->id,
        ]);

        return [$semillero, $proyecto];
    }

    public function test_project_ids_retorna_ids_de_proyectos_del_semillero(): void
    {
        [$semillero, $proyecto] = $this->crearSemilleroConProyecto();

        $ids = $semillero->projectIds();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $ids);
        $this->assertContains($proyecto->id, $ids);
    }

    public function test_project_ids_retorna_coleccion_vacia_sin_proyectos(): void
    {
        $creador = User::factory()->create();

        $semillero = Seedling::create([
            'research_group_id' => null,
            'creator_id'        => $creador->id,
            'nombre'            => 'Semillero Sin Proyectos',
            'codigo'            => 1002,
            'logo'              => 'default.png',
            'estado'            => EstadoEnum::Activo,
        ]);

        $ids = $semillero->projectIds();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $ids);
        $this->assertCount(0, $ids);
    }

    public function test_project_ids_coincide_con_query_directa_a_tabla_pivot(): void
    {
        [$semillero, $proyecto] = $this->crearSemilleroConProyecto();

        $idsEloquent = $semillero->projectIds()->sort()->values();
        $idsDirectos = DB::table('project_seedlings')
            ->where('seedling_id', $semillero->id)
            ->pluck('project_id')
            ->sort()
            ->values();

        $this->assertEquals($idsDirectos->toArray(), $idsEloquent->toArray(), 'projectIds() debe devolver los mismos IDs que la query directa');
    }
}
