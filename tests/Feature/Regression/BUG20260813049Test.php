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
 * BUG-20260813-049 — la página "Productos" del líder de semillero pasa de
 * ser una tabla plana con todas las evidencias mezcladas a estar agrupada
 * por proyecto: solo aparecen los proyectos que tienen al menos una
 * evidencia revisable, y dentro de cada uno se listan sus productos.
 */
class BUG20260813049Test extends TestCase
{
    use RefreshDatabase;

    private TrainingCenter $centro;
    private User $liderSemillero;
    private Seedling $semillero;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto BUG-049']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-049', 'department_id' => $depto->id]);
        $this->centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-049', 'codigo' => 'B049', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $this->liderSemillero = User::factory()->create(['training_center_id' => $this->centro->id]);
        $this->liderSemillero->assignRole('lider_semillero');

        $director = User::factory()->create(['training_center_id' => $this->centro->id]);
        $director->assignRole('director_semilleros');

        $this->semillero = Seedling::create([
            'creator_id' => $director->id,
            'leader_id' => $this->liderSemillero->id,
            'training_center_id' => $this->centro->id,
            'nombre' => 'Semillero BUG-049',
            'codigo' => 'S049',
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);
    }

    private function crearProyecto(string $nombre): Project
    {
        $liderProyecto = User::factory()->create(['training_center_id' => $this->centro->id]);
        $liderProyecto->assignRole('lider_proyecto');

        return Project::create([
            'project_creator_id' => $this->liderSemillero->id,
            'seedling_id' => $this->semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => ResearchLine::firstOrCreate(['nombre' => 'Linea BUG-049'])->id,
            'technological_line_id' => TechnologicalLine::firstOrCreate(['nombre' => 'Linea Tec BUG-049'])->id,
            'thematic_area_id' => ThematicArea::firstOrCreate(['nombre' => 'Area BUG-049'])->id,
            'project_modality_id' => ProjectModality::firstOrCreate(['nombre' => 'Modalidad BUG-049'])->id,
            'investigation_type_id' => InvestigationType::firstOrCreate(['nombre' => 'Tipo BUG-049'])->id,
            'nombre' => $nombre,
            'fecha_inicio' => now(),
            'estado' => 'activo',
        ]);
    }

    private function crearEvidencia(Project $proyecto, TipoEvidenciaEnum $tipo, ?EstadoRevisionEnum $estadoLider = null): ProjectEvidence
    {
        return ProjectEvidence::create([
            'project_id' => $proyecto->id,
            'tipo' => $tipo,
            'nombre' => 'Evidencia '.$tipo->value.' de '.$proyecto->nombre,
            'archivo' => 'evidencias-proyecto/test.pdf',
            'uploaded_by' => $proyecto->lider_proyecto_user_id,
            'estado_revision_lider' => $estadoLider,
        ]);
    }

    public function test_proyectos_sin_evidencias_no_aparecen_en_el_listado(): void
    {
        $this->crearProyecto('Proyecto sin evidencias');

        $response = $this->actingAs($this->liderSemillero)->get(route('lider-sem.productos'));

        $response->assertOk();
        $response->assertSee('No hay proyectos con evidencias pendientes de revisión todavía.');
        // El proyecto no debe tener su propia tarjeta de acordeón: cada
        // tarjeta va seguida de la etiqueta "Líder de Proyecto:" a los pocos
        // caracteres; si el nombre aparece solo (p.ej. en el sidebar), esa
        // combinación no se da.
        $content = $response->getContent();
        $posNombre = strpos($content, 'Proyecto sin evidencias');
        $this->assertNotFalse($posNombre);
        $siguiente = substr($content, $posNombre, 400);
        $this->assertStringNotContainsString('Líder de Proyecto:', $siguiente);
    }

    public function test_evidencias_quedan_agrupadas_bajo_su_propio_proyecto(): void
    {
        $proyectoA = $this->crearProyecto('Proyecto A BUG-049');
        $proyectoB = $this->crearProyecto('Proyecto B BUG-049');

        $this->crearEvidencia($proyectoA, TipoEvidenciaEnum::Formulacion, EstadoRevisionEnum::Pendiente);
        $this->crearEvidencia($proyectoB, TipoEvidenciaEnum::Ejecucion, EstadoRevisionEnum::Pendiente);

        $response = $this->actingAs($this->liderSemillero)->get(route('lider-sem.productos'));

        $response->assertOk();
        $full = $response->getContent();
        // Aísla el contenido principal (el acordeón de "Productos") del
        // resto del layout, ya que el sidebar también puede listar los
        // nombres de proyectos por separado para navegación.
        $inicio = strpos($full, 'class="space-y-4"');
        $fin = strpos($full, '</main>', $inicio);
        $content = substr($full, $inicio, $fin - $inicio);

        $this->assertStringContainsString('Proyecto A BUG-049', $content);
        $this->assertStringContainsString('Proyecto B BUG-049', $content);

        $posProyectoA = strpos($content, 'Proyecto A BUG-049');
        $posEvidenciaA = strpos($content, 'Evidencia formulacion de Proyecto A BUG-049');
        $posProyectoB = strpos($content, 'Proyecto B BUG-049');
        $posEvidenciaB = strpos($content, 'Evidencia ejecucion de Proyecto B BUG-049');

        // La evidencia de A aparece dentro del bloque de A (antes de que
        // empiece el bloque de B), y lo mismo para B.
        $this->assertTrue($posProyectoA < $posEvidenciaA);
        $this->assertTrue($posEvidenciaA < $posProyectoB);
        $this->assertTrue($posProyectoB < $posEvidenciaB);
    }

    public function test_proyecto_con_pendientes_muestra_contador_y_queda_expandido_por_defecto(): void
    {
        $proyecto = $this->crearProyecto('Proyecto con pendiente');
        $this->crearEvidencia($proyecto, TipoEvidenciaEnum::Formulacion, EstadoRevisionEnum::Pendiente);
        $this->crearEvidencia($proyecto, TipoEvidenciaEnum::Ejecucion, EstadoRevisionEnum::Pendiente);

        $response = $this->actingAs($this->liderSemillero)->get(route('lider-sem.productos'));

        $response->assertOk();
        $response->assertSee('2 pendientes');
        $response->assertSee('{ open: true }', false);
    }

    public function test_aprobar_sigue_funcionando_correctamente_tras_agrupar_por_proyecto(): void
    {
        $proyecto = $this->crearProyecto('Proyecto aprobación');
        $evidencia = $this->crearEvidencia($proyecto, TipoEvidenciaEnum::Formulacion, EstadoRevisionEnum::Pendiente);

        $response = $this->actingAs($this->liderSemillero)
            ->patch(route('lider-sem.productos.aprobar', $evidencia));

        $response->assertRedirect(route('lider-sem.productos'));
        $evidencia->refresh();
        $this->assertSame(EstadoRevisionEnum::Aprobado, $evidencia->estado_revision_lider);
    }
}
