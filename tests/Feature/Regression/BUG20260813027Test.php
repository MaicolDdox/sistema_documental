<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Services\LiderSemillero\RevisionEvidenciaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-027
 * Sistema de notificación tipo "punto rojo" (estilo WhatsApp: aparece
 * cuando hay algo sin ver, desaparece apenas se visita la página que
 * refleja el estado actual — sin campanita, sin historial, sin tabla nueva
 * de notificaciones):
 * - project_evidences ganó 2 columnas: visto_por_lider_proyecto_at y
 *   visto_por_lider_semillero_at.
 * - RevisionEvidenciaService resetea a null la columna del rol que NO
 *   actuó en cada aprobación/rechazo (aprobarEtapaLider/rechazarEtapaLider
 *   resetean visto_por_lider_proyecto_at; aprobarEtapaDirector/
 *   rechazarEtapaDirector resetean ambas).
 * - LiderProyecto\EvidenciaController::store() marca visto_por_lider_proyecto_at
 *   al momento de subir (no hay novedad que notificarse a sí mismo).
 * - LiderProyecto\DashboardController::index() y
 *   LiderSemillero\ProductosController::index() marcan como visto al cargar.
 * - El sidebar (resources/views/components/app-layout.blade.php) pinta un
 *   punto rojo en "Dashboard" (líder de proyecto) y en "Productos" (líder
 *   de semillero, solo cuando el director ya actuó sobre algo que este
 *   líder había aprobado — no se confunde con el badge de pendientes por
 *   revisar por primera vez).
 *
 * Bug adicional encontrado al escribir estos tests: ProductosController
 * (Líder de Semillero) y RevisionProductoController (Director de
 * Semilleros) ordenaban con `FIELD(...)`, función exclusiva de MySQL —
 * rompía con SQLSTATE[HY000] "no such function: FIELD" bajo SQLite (el
 * motor de tests, ver phpunit.xml). Nunca se había cubierto con un test
 * antes. Corregido con un `CASE WHEN` portable entre MySQL y SQLite.
 */
class BUG20260813027Test extends TestCase
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
            'codigo' => 929,
        ]);

        foreach (['productos.aprobar', 'productos.rechazar', 'productos.aprobar_final', 'productos.rechazar_final'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rolLiderSem = Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        $rolLiderSem->givePermissionTo(['productos.aprobar', 'productos.rechazar']);
        $rolDirector = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rolDirector->givePermissionTo(['productos.aprobar_final', 'productos.rechazar_final']);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);

        $director = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $director->assignRole('director_semilleros');

        $liderSemillero = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $liderSemillero->assignRole('lider_semillero');

        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $liderProyecto->assignRole('lider_proyecto');

        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 5000,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);

        $proyecto = Project::create([
            'project_creator_id' => $liderSemillero->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Test',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        return [$director, $liderSemillero, $liderProyecto, $proyecto];
    }

    public function test_subir_evidencia_no_genera_punto_rojo_para_el_propio_lider_de_proyecto(): void
    {
        [, , $liderProyecto, $proyecto] = $this->crearEscenario();

        $this->actingAs($liderProyecto)->post(route('lider-proyecto.evidencias.store'), [
            'tipo' => 'producto_final',
            'nombre' => 'Producto v1',
            'archivo' => \Illuminate\Http\UploadedFile::fake()->create('producto.pdf', 100),
        ]);

        $evidencia = ProjectEvidence::where('project_id', $proyecto->id)->firstOrFail();
        $this->assertNotNull($evidencia->visto_por_lider_proyecto_at);
    }

    public function test_aprobacion_del_lider_de_semillero_genera_punto_rojo_para_lider_de_proyecto(): void
    {
        [, $liderSemillero, $liderProyecto, $proyecto] = $this->crearEscenario();

        $evidencia = ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Producto v1',
            'uploaded_by' => $liderProyecto->id,
            'visto_por_lider_proyecto_at' => now(),
        ]);

        app(RevisionEvidenciaService::class)->aprobarEtapaLider($evidencia, $liderSemillero, null);

        $evidencia->refresh();
        $this->assertNull($evidencia->visto_por_lider_proyecto_at);

        $response = $this->actingAs($liderProyecto)->get(route('lider-proyecto.dashboard'));
        $response->assertOk();

        $evidencia->refresh();
        $this->assertNotNull($evidencia->visto_por_lider_proyecto_at, 'Visitar el dashboard debe marcar la novedad como vista.');
    }

    public function test_rechazo_del_director_genera_punto_rojo_para_lider_de_proyecto_y_lider_de_semillero(): void
    {
        [$director, $liderSemillero, $liderProyecto, $proyecto] = $this->crearEscenario();

        $evidencia = ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Producto v1',
            'uploaded_by' => $liderProyecto->id,
            'visto_por_lider_proyecto_at' => now(),
        ]);

        $servicio = app(RevisionEvidenciaService::class);
        $servicio->aprobarEtapaLider($evidencia, $liderSemillero, null);
        $evidencia->refresh();
        // El líder de proyecto ya vio la aprobación del líder de semillero.
        $evidencia->update(['visto_por_lider_proyecto_at' => now()]);

        $servicio->rechazarEtapaDirector($evidencia, $director, 'No cumple los requisitos.');
        $evidencia->refresh();

        $this->assertNull($evidencia->visto_por_lider_proyecto_at);
        $this->assertNull($evidencia->visto_por_lider_semillero_at);
    }

    public function test_visitar_productos_marca_como_visto_lo_que_el_director_resolvio(): void
    {
        [$director, $liderSemillero, $liderProyecto, $proyecto] = $this->crearEscenario();

        $evidencia = ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Producto v1',
            'uploaded_by' => $liderProyecto->id,
        ]);

        $servicio = app(RevisionEvidenciaService::class);
        $servicio->aprobarEtapaLider($evidencia, $liderSemillero, null);
        $servicio->aprobarEtapaDirector($evidencia, $director, null);

        $evidencia->refresh();
        $this->assertNull($evidencia->visto_por_lider_semillero_at);

        $response = $this->actingAs($liderSemillero)->get(route('lider-sem.productos'));
        $response->assertOk();

        $evidencia->refresh();
        $this->assertNotNull($evidencia->visto_por_lider_semillero_at);
    }

    public function test_evidencia_nueva_pendiente_no_activa_el_punto_rojo_de_notificacion_del_director(): void
    {
        [, $liderSemillero, $liderProyecto, $proyecto] = $this->crearEscenario();

        // Evidencia recién subida, nunca revisada por el líder de semillero
        // (revisado_lider_at sigue null) — esto debe verse reflejado en el
        // badge de "pendientes por revisar", NO en el punto rojo de
        // "el director actuó sobre algo que ya aprobaste".
        ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Producto nuevo sin revisar',
            'uploaded_by' => $liderProyecto->id,
            'estado_revision_lider' => \App\Enums\EstadoRevisionEnum::Pendiente,
        ]);

        $response = $this->actingAs($liderSemillero)->get(route('lider-sem.dashboard'));

        $response->assertOk();
        // No debe existir el <span> del punto rojo de notificación del
        // director en el ítem "Productos" — esta evidencia todavía está en
        // su primera revisión, no es una acción del director sobre algo ya
        // aprobado por este líder.
        $response->assertDontSee('El Director de Semilleros tomó una acción sobre un producto', false);
    }
}
