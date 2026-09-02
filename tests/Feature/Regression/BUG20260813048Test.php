<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\InvestigationType;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * BUG-20260813-048 — la barra de avances del proyecto (vista de
 * lider_semillero) pasa de ser un placeholder basado en fechas a reflejar
 * el avance real por fases de evidencia: Formulación 30%, Ejecución 50%,
 * Producto Final 20% (con su doble aprobación existente sin cambios).
 * Formulación y Ejecución usan una sola etapa de aprobación (líder de
 * semillero), sin pasar por el Director de Semilleros.
 */
class BUG20260813048Test extends TestCase
{
    use RefreshDatabase;

    private TrainingCenter $centro;
    private User $liderProyecto;
    private User $liderSemillero;
    private User $directorSemilleros;
    private Seedling $semillero;
    private Project $proyecto;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto BUG-048']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-048', 'department_id' => $depto->id]);
        $this->centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-048', 'codigo' => 'B048', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $rl = ResearchLine::create(['nombre' => 'Linea BUG-048']);
        $tl = TechnologicalLine::create(['nombre' => 'Linea tec BUG-048']);
        $ta = ThematicArea::create(['nombre' => 'Area BUG-048']);
        $pm = ProjectModality::create(['nombre' => 'Modalidad BUG-048']);
        $it = InvestigationType::create(['nombre' => 'Tipo BUG-048']);

        $this->liderProyecto = User::factory()->create(['training_center_id' => $this->centro->id]);
        $this->liderProyecto->assignRole('lider_proyecto');

        $this->liderSemillero = User::factory()->create(['training_center_id' => $this->centro->id]);
        $this->liderSemillero->assignRole('lider_semillero');

        $this->directorSemilleros = User::factory()->create(['training_center_id' => $this->centro->id]);
        $this->directorSemilleros->assignRole('director_semilleros');

        $this->semillero = Seedling::create([
            'creator_id' => $this->directorSemilleros->id,
            'leader_id' => $this->liderSemillero->id,
            'training_center_id' => $this->centro->id,
            'nombre' => 'Semillero BUG-048',
            'codigo' => 'S048',
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->proyecto = Project::create([
            'project_creator_id' => $this->liderSemillero->id,
            'seedling_id' => $this->semillero->id,
            'lider_proyecto_user_id' => $this->liderProyecto->id,
            'research_line_id' => $rl->id,
            'technological_line_id' => $tl->id,
            'thematic_area_id' => $ta->id,
            'project_modality_id' => $pm->id,
            'investigation_type_id' => $it->id,
            'nombre' => 'Proyecto BUG-048',
            'fecha_inicio' => now()->subMonths(2),
            'estado' => 'activo',
        ]);
    }

    private function crearEvidencia(TipoEvidenciaEnum $tipo, ?EstadoRevisionEnum $estadoLider = null, ?EstadoRevisionEnum $estadoDirector = null): ProjectEvidence
    {
        return ProjectEvidence::create([
            'project_id' => $this->proyecto->id,
            'tipo' => $tipo,
            'nombre' => 'Evidencia '.$tipo->value,
            'archivo' => 'evidencias-proyecto/test.pdf',
            'uploaded_by' => $this->liderProyecto->id,
            'estado_revision_lider' => $estadoLider,
            'estado_revision_director' => $estadoDirector,
        ]);
    }

    public function test_formulario_de_subida_tiene_las_4_opciones_en_el_orden_exacto(): void
    {
        $response = $this->actingAs($this->liderProyecto)->get(route('lider-proyecto.evidencias.index'));

        $response->assertOk();
        $content = $response->getContent();

        $posDesarrollo = strpos($content, 'value="desarrollo"');
        $posFormulacion = strpos($content, 'value="formulacion"');
        $posEjecucion = strpos($content, 'value="ejecucion"');
        $posProductoFinal = strpos($content, 'value="producto_final"');

        $this->assertNotFalse($posDesarrollo);
        $this->assertNotFalse($posFormulacion);
        $this->assertNotFalse($posEjecucion);
        $this->assertNotFalse($posProductoFinal);
        $this->assertTrue($posDesarrollo < $posFormulacion);
        $this->assertTrue($posFormulacion < $posEjecucion);
        $this->assertTrue($posEjecucion < $posProductoFinal);
    }

    public function test_subir_evidencia_de_formulacion_queda_pendiente_de_revision_del_lider(): void
    {
        $archivo = \Illuminate\Http\UploadedFile::fake()->create('formulacion.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->liderProyecto)->post(route('lider-proyecto.evidencias.store'), [
            'tipo' => 'formulacion',
            'nombre' => 'Documento de formulación',
            'archivo' => $archivo,
        ]);

        $response->assertRedirect(route('lider-proyecto.evidencias.index'));
        $this->assertDatabaseHas('project_evidences', [
            'project_id' => $this->proyecto->id,
            'tipo' => 'formulacion',
            'estado_revision_lider' => 'pendiente',
            'estado_revision_director' => null,
        ]);
    }

    public function test_lider_de_semillero_ve_y_aprueba_formulacion_sin_involucrar_al_director(): void
    {
        $evidencia = $this->crearEvidencia(TipoEvidenciaEnum::Formulacion, EstadoRevisionEnum::Pendiente);

        $indexResponse = $this->actingAs($this->liderSemillero)->get(route('lider-sem.productos'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Formulación');

        $response = $this->actingAs($this->liderSemillero)
            ->patch(route('lider-sem.productos.aprobar', $evidencia));

        $response->assertRedirect(route('lider-sem.productos'));
        $evidencia->refresh();

        $this->assertSame(EstadoRevisionEnum::Aprobado, $evidencia->estado_revision_lider);
        $this->assertNull($evidencia->estado_revision_director);
    }

    public function test_director_no_puede_ver_evidencias_de_formulacion_o_ejecucion(): void
    {
        $this->crearEvidencia(TipoEvidenciaEnum::Formulacion, EstadoRevisionEnum::Aprobado);
        $this->crearEvidencia(TipoEvidenciaEnum::Ejecucion, EstadoRevisionEnum::Aprobado);

        $response = $this->actingAs($this->directorSemilleros)->get(route('dir-sem.productos.index'));

        $response->assertOk();
        $response->assertDontSee('Evidencia formulacion');
        $response->assertDontSee('Evidencia ejecucion');
    }

    public function test_producto_final_sigue_exigiendo_doble_aprobacion_sin_cambios(): void
    {
        $evidencia = $this->crearEvidencia(TipoEvidenciaEnum::ProductoFinal, EstadoRevisionEnum::Pendiente);

        $this->actingAs($this->liderSemillero)
            ->patch(route('lider-sem.productos.aprobar', $evidencia))
            ->assertRedirect(route('lider-sem.productos'));

        $evidencia->refresh();
        $this->assertSame(EstadoRevisionEnum::Aprobado, $evidencia->estado_revision_lider);
        $this->assertSame(EstadoRevisionEnum::Pendiente, $evidencia->estado_revision_director);

        $this->actingAs($this->directorSemilleros)
            ->patch(route('dir-sem.productos.aprobar', $evidencia))
            ->assertRedirect(route('dir-sem.productos.index'));

        $evidencia->refresh();
        $this->assertSame(EstadoRevisionEnum::Aprobado, $evidencia->estado_revision_director);
    }

    public function test_avance_es_cero_sin_evidencias_aprobadas(): void
    {
        $response = $this->actingAs($this->liderSemillero)->get(route('lider-sem.proyectos'));

        $response->assertOk();
        $response->assertViewHas('proyectos', function ($proyectos) {
            return $proyectos->first()->avance === 0;
        });
    }

    public function test_avance_suma_30_por_formulacion_aprobada(): void
    {
        $this->crearEvidencia(TipoEvidenciaEnum::Formulacion, EstadoRevisionEnum::Aprobado);

        $response = $this->actingAs($this->liderSemillero)->get(route('lider-sem.proyectos'));

        $response->assertViewHas('proyectos', function ($proyectos) {
            return $proyectos->first()->avance === 30;
        });
    }

    public function test_avance_suma_30_mas_50_por_formulacion_y_ejecucion_aprobadas(): void
    {
        $this->crearEvidencia(TipoEvidenciaEnum::Formulacion, EstadoRevisionEnum::Aprobado);
        $this->crearEvidencia(TipoEvidenciaEnum::Ejecucion, EstadoRevisionEnum::Aprobado);

        $response = $this->actingAs($this->liderSemillero)->get(route('lider-sem.proyectos'));

        $response->assertViewHas('proyectos', function ($proyectos) {
            return $proyectos->first()->avance === 80;
        });
    }

    public function test_avance_llega_a_100_solo_cuando_producto_final_tiene_doble_aprobacion(): void
    {
        $this->crearEvidencia(TipoEvidenciaEnum::Formulacion, EstadoRevisionEnum::Aprobado);
        $this->crearEvidencia(TipoEvidenciaEnum::Ejecucion, EstadoRevisionEnum::Aprobado);
        // Producto final aprobado solo por el líder (etapa 1) todavía no debe sumar el 20%.
        $this->crearEvidencia(TipoEvidenciaEnum::ProductoFinal, EstadoRevisionEnum::Aprobado, EstadoRevisionEnum::Pendiente);

        $response = $this->actingAs($this->liderSemillero)->get(route('lider-sem.proyectos'));
        $response->assertViewHas('proyectos', function ($proyectos) {
            return $proyectos->first()->avance === 80;
        });

        // Al aprobar también la etapa del director, el avance llega a 100%.
        $this->proyecto->projectEvidences()
            ->where('tipo', TipoEvidenciaEnum::ProductoFinal)
            ->update(['estado_revision_director' => EstadoRevisionEnum::Aprobado]);

        $response = $this->actingAs($this->liderSemillero)->get(route('lider-sem.proyectos'));
        $response->assertViewHas('proyectos', function ($proyectos) {
            return $proyectos->first()->avance === 100;
        });
    }
}
