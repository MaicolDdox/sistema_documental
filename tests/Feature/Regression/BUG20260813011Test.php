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
 * Regresión: BUG-20260813-011
 * Refinamiento de la fusión de BUG-20260813-010: en vez de tabs de clic, el
 * detalle del semillero ahora es una sola página con secciones apiladas
 * (scrolleable). La sección "Proyectos" gana un buscador por nombre y, al
 * seleccionar un proyecto, muestra su Líder de Proyecto, sus integrantes
 * (aprendices vía ProjectLearner), sus co-investigadores vinculados
 * (ProjectAuthor activo), sus evidencias de desarrollo y su producto final
 * con el estado de las 2 etapas de revisión.
 * Se eliminó la sección "Co-investigadores" a nivel de TODO el semillero
 * (redundante, ahora va por proyecto) y las secciones "Productos" (código
 * muerto: `$productos = [];` hardcodeado, nunca mostró datos reales) y
 * "Evidencias" (duplicaba exactamente los mismos datos que "Documentos").
 * Corregido: 2026-08-13.
 */
class BUG20260813011Test extends TestCase
{
    use RefreshDatabase;

    private function crearEscenarioCompleto(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 912,
        ]);

        foreach ([
            'semilleros.listar', 'semilleros.ver_detalle', 'semilleros.ver_integrantes', 'semilleros.ver_proyectos',
            'documentos.listar', 'documentos.subir', 'documentos.eliminar_propio',
            'reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.proyectos_por_estado', 'reportes.exportar_pdf_excel',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rol->givePermissionTo([
            'semilleros.listar', 'semilleros.ver_detalle', 'semilleros.ver_integrantes', 'semilleros.ver_proyectos',
            'documentos.listar', 'documentos.subir', 'documentos.eliminar_propio',
            'reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.proyectos_por_estado', 'reportes.exportar_pdf_excel',
        ]);
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);

        $director = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $director->assignRole('director_semilleros');

        $semillero = Seedling::create([
            'creator_id' => $director->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 1901,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $liderProyecto = User::factory()->create([
            'training_center_id' => $centro->id,
            'email' => 'liderproyecto-test@example.com',
        ]);
        $liderProyecto->assignRole('lider_proyecto');

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);
        $proyecto = Project::create([
            'project_creator_id' => $director->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Investigación Aplicada',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        ProjectLearner::create([
            'project_id' => $proyecto->id,
            'created_by_user_id' => $liderProyecto->id,
            'nombre_completo' => 'Aprendiz De Prueba',
            'numero_documento' => '123123123',
            'ficha' => '2600099',
            'nombre_tecnologo' => 'ADSO',
        ]);

        $coinvestigador = User::factory()->create(['email' => 'coinv-test@example.com']);
        ProjectAuthor::create([
            'project_id' => $proyecto->id,
            'user_id' => $coinvestigador->id,
            'activo' => true,
        ]);

        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::Desarrollo,
            'nombre' => 'Avance De Desarrollo Test',
            'uploaded_by' => $liderProyecto->id,
        ]);

        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Producto Final Test',
            'uploaded_by' => $liderProyecto->id,
        ]);

        return [$director, $centro, $semillero, $proyecto];
    }

    public function test_pagina_semillero_es_secciones_apiladas_sin_tabs_de_clic(): void
    {
        [$director, , $semillero] = $this->crearEscenarioCompleto();

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.show', $semillero));

        $response->assertOk();
        $response->assertDontSee('activeTab');
    }

    public function test_seccion_productos_y_evidencias_muertas_ya_no_existen(): void
    {
        [$director, , $semillero] = $this->crearEscenarioCompleto();

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.show', $semillero));

        $response->assertOk();
        $response->assertDontSee('No se encontraron productos desarrollados por este semillero');
    }

    public function test_seccion_proyectos_tiene_buscador_por_nombre(): void
    {
        [$director, , $semillero] = $this->crearEscenarioCompleto();

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.show', $semillero));

        $response->assertOk();
        $response->assertSee('Buscar proyecto por nombre');
    }

    public function test_detalle_proyecto_muestra_lider_integrantes_coinvestigadores_evidencias_y_producto_final(): void
    {
        [$director, , $semillero, $proyecto] = $this->crearEscenarioCompleto();

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.show', $semillero));

        $response->assertOk();
        $response->assertSee('Proyecto Investigación Aplicada');
        $response->assertSee('liderproyecto-test@example.com');
        $response->assertSee('Aprendiz De Prueba');
        $response->assertSee('coinv-test@example.com');
        $response->assertSee('Avance De Desarrollo Test');
        $response->assertSee('Producto Final Test');
    }
}
