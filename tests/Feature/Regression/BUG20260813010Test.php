<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectAuthor;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-010
 * Fusión de los ítems de sidebar "Semilleros" / "Documentos" / "Reportes"
 * (Director de Semilleros) en una sola estructura:
 * - "Administrar Semilleros": mismo listado/creación/desactivación de siempre.
 * - "Semilleros" (desplegable): lista todos los semilleros (activos e
 *   inactivos) y cada uno lleva al detalle (show), que ahora tiene tabs de
 *   Documentos, Co-investigadores y Reportes además de los que ya existían.
 * También: la card del dashboard "Asesores vinculados" pasó a mostrar
 * "Co-investigadores asociados" con datos reales (antes contaba
 * seedling_advisors, que ya no aplica al rediseño).
 * Corregido: 2026-08-13.
 */
class BUG20260813010Test extends TestCase
{
    use RefreshDatabase;

    private function crearDirectorConSemillero(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 911,
        ]);

        foreach ([
            'semilleros.listar', 'semilleros.ver_detalle', 'semilleros.ver_integrantes',
            'semilleros.ver_asesores', 'semilleros.ver_proyectos', 'semilleros.ver_productos', 'semilleros.ver_evidencias',
            'documentos.listar', 'documentos.subir', 'documentos.eliminar_propio',
            'reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.proyectos_por_estado', 'reportes.exportar_pdf_excel',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rol->givePermissionTo([
            'semilleros.listar', 'semilleros.ver_detalle', 'semilleros.ver_integrantes',
            'semilleros.ver_asesores', 'semilleros.ver_proyectos', 'semilleros.ver_productos', 'semilleros.ver_evidencias',
            'documentos.listar', 'documentos.subir', 'documentos.eliminar_propio',
            'reportes.semilleros_con_metricas', 'reportes.aprendices_por_semillero', 'reportes.proyectos_por_estado', 'reportes.exportar_pdf_excel',
        ]);
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);

        $director = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $director->assignRole('director_semilleros');

        $semillero = Seedling::create([
            'creator_id' => $director->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 1801,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        return [$director, $centro, $semillero];
    }

    public function test_rutas_documentos_index_y_reportes_index_ya_no_existen(): void
    {
        $this->assertFalse(Route::has('dir-sem.documentos.index'));
        $this->assertFalse(Route::has('dir-sem.documentos.create'));
        $this->assertFalse(Route::has('dir-sem.reportes.index'));
        $this->assertTrue(Route::has('dir-sem.documentos.store'));
        $this->assertTrue(Route::has('dir-sem.documentos.destroy'));
        $this->assertTrue(Route::has('dir-sem.reportes.exportar'));
    }

    public function test_sidebar_muestra_administrar_semilleros_y_dropdown_semilleros(): void
    {
        [$director, , $semillero] = $this->crearDirectorConSemillero();

        $response = $this->actingAs($director)->get(route('dir-sem.dashboard'));

        $response->assertOk();
        $response->assertSee('Administrar Semilleros');
        $response->assertDontSee('GESTIÓN SEMILLEROS');
        $response->assertSee($semillero->nombre);
    }

    public function test_detalle_semillero_tiene_secciones_documentos_y_reportes(): void
    {
        // Nota: la sección "Co-investigadores" a nivel de TODO el semillero se
        // eliminó en BUG-20260813-011 (redundante, ahora se ven por proyecto
        // dentro de la sección "Proyectos"). Ver ese test para la cobertura vigente.
        [$director, , $semillero] = $this->crearDirectorConSemillero();

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.show', $semillero));

        $response->assertOk();
        $response->assertSee('Documentos');
        $response->assertSee('Reportes');
        $response->assertDontSee('Módulo de asesores en desarrollo');
    }

    public function test_tab_proyectos_muestra_nombre_real_del_proyecto_no_stale_fields(): void
    {
        [$director, $centro, $semillero] = $this->crearDirectorConSemillero();

        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id]);
        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);
        Project::create([
            'project_creator_id' => $director->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto De Prueba Real',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.show', $semillero));

        $response->assertOk();
        $response->assertSee('Proyecto De Prueba Real');
    }

    public function test_tab_coinvestigadores_muestra_coinvestigadores_reales_vinculados(): void
    {
        [$director, $centro, $semillero] = $this->crearDirectorConSemillero();

        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id]);
        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);
        $proyecto = Project::create([
            'project_creator_id' => $director->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Test',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        $coinvestigador = User::factory()->create(['email' => 'coinv@test.com']);
        ProjectAuthor::create([
            'project_id' => $proyecto->id,
            'user_id' => $coinvestigador->id,
            'activo' => true,
        ]);

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.show', $semillero));

        $response->assertOk();
        $response->assertSee('coinv@test.com');
    }

    public function test_subir_documento_scoped_a_semillero_redirige_a_show(): void
    {
        [$director, , $semillero] = $this->crearDirectorConSemillero();

        $archivo = \Illuminate\Http\UploadedFile::fake()->create('acta.pdf', 100, 'application/pdf');

        $response = $this->actingAs($director)->post(route('dir-sem.documentos.store'), [
            'nombre' => 'Acta de reunión',
            'semillero_id' => $semillero->id,
            'archivo' => $archivo,
        ]);

        $response->assertRedirect(route('dir-sem.semilleros.show', $semillero));
        $this->assertDatabaseHas('seedling_files', ['seedling_id' => $semillero->id, 'archivo' => 'Acta de reunión']);
    }

    public function test_dashboard_muestra_coinvestigadores_asociados_no_asesores(): void
    {
        [$director] = $this->crearDirectorConSemillero();

        $response = $this->actingAs($director)->get(route('dir-sem.dashboard'));

        $response->assertOk();
        $response->assertSee('Co-investigadores asociados');
        $response->assertDontSee('Asesores vinculados');
    }
}
