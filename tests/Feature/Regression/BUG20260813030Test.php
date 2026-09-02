<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoEvidenciaEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\InvestigationType;
use App\Models\MincienciasProduct;
use App\Models\MincienciasProductFile;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\SeedlingFile;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

/**
 * BUG-20260813-030 — la opción de "ver en el navegador" (inline) se
 * eliminó del sistema completo (decisión del usuario): solo queda
 * descarga. Este test verifica dos cosas por HTTP real (sesión + todo el
 * middleware de por medio) en cada rol: (1) la ruta ".ver" ya no existe,
 * (2) "Descargar" responde attachment con el nombre + extensión correctos.
 */
class BUG20260813030Test extends TestCase
{
    use RefreshDatabase;

    private TrainingCenter $centro;
    private User $coInvestigador;
    private User $liderProyecto;
    private User $liderSemillero;
    private User $directorSemilleros;
    private User $adminSistema;
    private Seedling $semillero;
    private Project $proyecto;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto test']);
        $ciudad = City::create(['nombre' => 'Ciudad test', 'department_id' => $depto->id]);
        $this->centro = TrainingCenter::create([
            'nombre' => 'Centro test', 'codigo' => 'T001', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $rl = ResearchLine::create(['nombre' => 'Linea test']);
        $tl = TechnologicalLine::create(['nombre' => 'Linea tec test']);
        $ta = ThematicArea::create(['nombre' => 'Area test']);
        $pm = ProjectModality::create(['nombre' => 'Modalidad test']);
        $it = InvestigationType::create(['nombre' => 'Tipo test']);

        $this->coInvestigador = User::factory()->create(['training_center_id' => null]);
        $this->coInvestigador->assignRole('co_investigador');

        $this->liderProyecto = User::factory()->create(['training_center_id' => $this->centro->id]);
        $this->liderProyecto->assignRole('lider_proyecto');

        $this->liderSemillero = User::factory()->create(['training_center_id' => $this->centro->id]);
        $this->liderSemillero->assignRole('lider_semillero');

        $this->directorSemilleros = User::factory()->create(['training_center_id' => $this->centro->id]);
        $this->directorSemilleros->assignRole('director_semilleros');

        $this->adminSistema = User::factory()->create(['training_center_id' => $this->centro->id]);
        $this->adminSistema->assignRole('administrador_sistema');

        $this->semillero = Seedling::create([
            'creator_id' => $this->directorSemilleros->id,
            'leader_id' => $this->liderSemillero->id,
            'training_center_id' => $this->centro->id,
            'nombre' => 'Semillero test',
            'codigo' => 1001,
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
            'nombre' => 'Proyecto test',
            'fecha_inicio' => now(),
            'estado' => 'activo',
        ]);
        $this->proyecto->authors()->attach($this->coInvestigador->id, ['activo' => true]);
    }

    private function pdf(string $path): string
    {
        Storage::disk('public')->put($path, '%PDF-1.4 contenido de prueba');

        return $path;
    }

    /**
     * @param  array<string>  $rutasVerEliminadas
     */
    private function assertRutasVerNoExisten(array $rutasVerEliminadas): void
    {
        foreach ($rutasVerEliminadas as $nombre) {
            try {
                route($nombre, 1);
                $this->fail("La ruta '{$nombre}' todavía existe — debía eliminarse junto con la opción de Ver.");
            } catch (RouteNotFoundException) {
                $this->assertTrue(true);
            }
        }
    }

    private function assertDescargaAttachmentConExtension(string $routeName, $param): void
    {
        $response = $this->get(route($routeName, $param));
        $response->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'), "{$routeName}: Content-Type incorrecto");
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment', $disposition, "{$routeName}: no es attachment");
        $this->assertStringContainsString('.pdf', $disposition, "{$routeName}: falta la extensión en el nombre de descarga");
    }

    public function test_rutas_ver_eliminadas_en_todo_el_sistema(): void
    {
        $this->assertRutasVerNoExisten([
            'co-investigador.archivos.ver',
            'co-investigador.evidencias.ver',
            'admin.minciencias.archivos.ver',
            'lider-proyecto.evidencias.ver',
            'lider-sem.evidencias.ver',
            'lider-sem.productos.ver',
            'lider-sem.archivos.ver',
            'lider-sem.doc-interna.ver',
            'dir-sem.evidencias.ver',
            'dir-sem.productos.ver',
            'dir-sem.documentos.ver',
        ]);
    }

    public function test_descargar_sigue_funcionando_con_attachment_en_todos_los_roles(): void
    {
        // co_investigador — Producto Minciencias
        $producto = MincienciasProduct::create([
            'user_id' => $this->coInvestigador->id,
            'training_center_id' => $this->centro->id,
            'research_line_id' => ResearchLine::first()->id,
            'nombre' => 'Producto Minciencias test',
            'estado' => 'activo',
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);
        $archivoMinc = MincienciasProductFile::create([
            'minciencias_product_id' => $producto->id,
            'archivo' => $this->pdf('minciencias-productos/co_inv.pdf'),
            'uploaded_by' => $this->coInvestigador->id,
        ]);
        $this->actingAs($this->coInvestigador);
        $this->assertDescargaAttachmentConExtension('co-investigador.archivos.descargar', $archivoMinc);

        // administrador_sistema — mismo archivo, vista de aprobación
        $this->actingAs($this->adminSistema);
        $this->assertDescargaAttachmentConExtension('admin.minciencias.archivos.descargar', $archivoMinc);

        // co_investigador — evidencia de proyecto vinculado
        $evCo = ProjectEvidence::create([
            'project_id' => $this->proyecto->id,
            'tipo' => TipoEvidenciaEnum::Desarrollo,
            'nombre' => 'Evidencia co-investigador',
            'archivo' => $this->pdf('evidencias-proyecto/co_inv_ev.pdf'),
            'uploaded_by' => $this->coInvestigador->id,
        ]);
        $this->actingAs($this->coInvestigador);
        $this->assertDescargaAttachmentConExtension('co-investigador.evidencias.descargar', $evCo);

        // lider_proyecto — su propia evidencia
        $evLp = ProjectEvidence::create([
            'project_id' => $this->proyecto->id,
            'tipo' => TipoEvidenciaEnum::Desarrollo,
            'nombre' => 'Evidencia lider proyecto',
            'archivo' => $this->pdf('evidencias-proyecto/lp_ev.pdf'),
            'uploaded_by' => $this->liderProyecto->id,
        ]);
        $this->actingAs($this->liderProyecto);
        $this->assertDescargaAttachmentConExtension('lider-proyecto.evidencias.descargar', $evLp);

        // lider_semillero — tab de evidencias del proyecto
        $this->actingAs($this->liderSemillero);
        $this->assertDescargaAttachmentConExtension('lider-sem.evidencias.descargar', $evLp);

        // lider_semillero — revisión 1a etapa de producto final
        $evProdFinal = ProjectEvidence::create([
            'project_id' => $this->proyecto->id,
            'tipo' => TipoEvidenciaEnum::ProductoFinal,
            'nombre' => 'Producto final test',
            'archivo' => $this->pdf('evidencias-proyecto/prod_final.pdf'),
            'uploaded_by' => $this->liderProyecto->id,
            'estado_revision_lider' => EstadoRevisionEnum::Pendiente,
        ]);
        $this->actingAs($this->liderSemillero);
        $this->assertDescargaAttachmentConExtension('lider-sem.productos.descargar', $evProdFinal);

        // director_semilleros — tab de evidencias del proyecto
        $this->actingAs($this->directorSemilleros);
        $this->assertDescargaAttachmentConExtension('dir-sem.evidencias.descargar', $evLp);

        // director_semilleros — aprobación definitiva (2a etapa)
        $evProdFinal->update(['estado_revision_lider' => EstadoRevisionEnum::Aprobado]);
        $this->actingAs($this->directorSemilleros);
        $this->assertDescargaAttachmentConExtension('dir-sem.productos.descargar', $evProdFinal);

        // director_semilleros — documentos de semillero
        $doc = SeedlingFile::create([
            'seedling_id' => $this->semillero->id,
            'user_id' => $this->directorSemilleros->id,
            'archivo' => 'documento_prueba.pdf',
            'url_archivo' => $this->pdf('documentos/doc_test.pdf'),
        ]);
        $this->actingAs($this->directorSemilleros);
        $this->assertDescargaAttachmentConExtension('dir-sem.documentos.descargar', $doc);

        // lider_semillero — archivos de semillero (módulo ya existente, control)
        $archivoSem = SeedlingFile::create([
            'seedling_id' => $this->semillero->id,
            'user_id' => $this->liderSemillero->id,
            'archivo' => 'archivo_prueba.pdf',
            'url_archivo' => $this->pdf('archivos_semillero/'.$this->semillero->id.'/archivo_prueba.pdf'),
        ]);
        $this->actingAs($this->liderSemillero);
        $this->assertDescargaAttachmentConExtension('lider-sem.archivos.descargar', $archivoSem);
    }
}
