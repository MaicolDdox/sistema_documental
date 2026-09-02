<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectAuthor;
use App\Models\ProjectEvidence;
use App\Models\ProjectLearner;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-015
 * El ítem "Proyectos" del sidebar de Líder de Semillero pasó de ser un link
 * directo (a un listado) a un desplegable que lista los proyectos del
 * semillero. Cada proyecto lleva a una página nueva de detalle (con scroll)
 * con 3 cards: 1) descripción específica del proyecto, 2) integrantes
 * (Líder de Proyecto, aprendices registrados, co-investigadores), 3) avances
 * del proyecto (evidencias tipo=desarrollo).
 * Corregido: 2026-08-13.
 */
class BUG20260813015Test extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 916,
        ]);

        foreach (['proyectos.ver_detalle', 'proyectos.editar', 'proyectos.crear'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        $rol->givePermissionTo(['proyectos.ver_detalle', 'proyectos.editar', 'proyectos.crear']);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);

        $lider = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $lider->assignRole('lider_semillero');

        $semillero = Seedling::create([
            'creator_id' => $lider->id,
            'leader_id' => $lider->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 2201,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $liderProyecto = User::factory()->create([
            'training_center_id' => $centro->id,
            'email' => 'liderproyecto-015@example.com',
        ]);
        $liderProyecto->assignRole('lider_proyecto');

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);
        $proyecto = Project::create([
            'project_creator_id' => $lider->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Detalle Test',
            'descripcion' => 'Una descripción breve pero específica.',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        ProjectLearner::create([
            'project_id' => $proyecto->id,
            'created_by_user_id' => $liderProyecto->id,
            'nombre_completo' => 'Aprendiz Detalle Test',
            'numero_documento' => '444555666',
            'ficha' => '2600055',
            'nombre_tecnologo' => 'ADSO',
        ]);

        $coinvestigador = User::factory()->create(['email' => 'coinv-015@example.com']);
        ProjectAuthor::create([
            'project_id' => $proyecto->id,
            'user_id' => $coinvestigador->id,
            'activo' => true,
        ]);

        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::Desarrollo,
            'nombre' => 'Avance Semanal Test',
            'uploaded_by' => $liderProyecto->id,
        ]);

        return [$lider, $semillero, $proyecto];
    }

    public function test_sidebar_proyectos_es_desplegable_y_lista_proyectos(): void
    {
        [$lider, , $proyecto] = $this->crearEscenario();

        $response = $this->actingAs($lider)->get(route('lider-sem.dashboard'));

        $response->assertOk();
        $response->assertSee('openProyectos', false);
        $response->assertSee($proyecto->nombre);
    }

    public function test_pagina_detalle_proyecto_muestra_las_3_cards(): void
    {
        [$lider, , $proyecto] = $this->crearEscenario();

        $response = $this->actingAs($lider)->get(route('lider-sem.proyectos.show', $proyecto));

        $response->assertOk();
        $response->assertSee('Una descripción breve pero específica.');
        $response->assertSee('Integrantes del Proyecto');
        $response->assertSee('liderproyecto-015@example.com');
        $response->assertSee('Aprendiz Detalle Test');
        $response->assertSee('coinv-015@example.com');
        $response->assertSee('Avances del Proyecto');
        $response->assertSee('Avance Semanal Test');
    }

    public function test_lider_no_puede_ver_proyecto_de_otro_semillero(): void
    {
        [$lider] = $this->crearEscenario();

        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $otroCentro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Otro Centro',
            'codigo' => 917,
        ]);
        $otroLiderSem = User::factory()->create(['training_center_id' => $otroCentro->id]);
        $otroSemillero = Seedling::create([
            'creator_id' => $otroLiderSem->id,
            'leader_id' => $otroLiderSem->id,
            'training_center_id' => $otroCentro->id,
            'nombre' => 'Otro Semillero',
            'codigo' => 2202,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);
        $otroLiderProyecto = User::factory()->create(['training_center_id' => $otroCentro->id]);
        $researchLine = ResearchLine::create(['nombre' => 'Línea Otra', 'estado' => EstadoEnum::Activo]);
        $otroProyecto = Project::create([
            'project_creator_id' => $otroLiderSem->id,
            'seedling_id' => $otroSemillero->id,
            'lider_proyecto_user_id' => $otroLiderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Ajeno',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        $response = $this->actingAs($lider)->get(route('lider-sem.proyectos.show', $otroProyecto));

        $response->assertForbidden();
    }
}
