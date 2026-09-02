<?php

namespace Tests\Feature\Regression;

use App\Models\InvestigationType;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use Database\Seeders\AreasTematicasSeeder;
use Database\Seeders\LineasInvestigacionesSeeder;
use Database\Seeders\LineasTecnologicasSeeder;
use Database\Seeders\ModalidadesProyectosSeeder;
use Database\Seeders\TiposInvestigacionesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-028
 * Los 5 catálogos de formularios (líneas de investigación, líneas
 * tecnológicas, áreas temáticas, modalidad, tipo de investigación) se
 * reemplazaron por las listas reales de SENA. Los seeders quedaron
 * desincronizados una vez de la BD real antes de este fix; este test
 * evita que un futuro `migrate:fresh --seed` vuelva a desincronizarlos.
 */
class BUG20260813028Test extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_de_catalogos_cargan_las_listas_reales_esperadas(): void
    {
        (new LineasInvestigacionesSeeder)->run();
        (new LineasTecnologicasSeeder)->run();
        (new AreasTematicasSeeder)->run();
        (new ModalidadesProyectosSeeder)->run();
        (new TiposInvestigacionesSeeder)->run();

        $this->assertSame(6, ResearchLine::count());
        $this->assertDatabaseHas('research_lines', ['nombre' => 'Innovación y Transformación Educativa']);

        $this->assertSame(6, TechnologicalLine::count());
        $this->assertDatabaseHas('technological_lines', ['nombre' => 'Línea de TICs e Inteligencia Artificial']);

        $this->assertSame(7, ThematicArea::count());
        $this->assertDatabaseHas('thematic_areas', ['nombre' => 'Agroindustrial']);

        $this->assertSame(4, ProjectModality::count());
        $this->assertDatabaseHas('project_modalities', ['nombre' => 'Recursos Internos SENA']);

        // Único requisito explícito para este catálogo: agregar "Innovación"
        // sin tocar los 4 tipos preexistentes.
        $this->assertSame(5, InvestigationType::count());
        $this->assertDatabaseHas('investigation_types', ['nombre' => 'Innovación']);
        $this->assertDatabaseHas('investigation_types', ['nombre' => 'Investigación Aplicada']);
    }

    public function test_seeders_son_idempotentes(): void
    {
        (new TiposInvestigacionesSeeder)->run();
        (new TiposInvestigacionesSeeder)->run();

        $this->assertSame(5, InvestigationType::count());
        $this->assertSame(1, InvestigationType::where('nombre', 'Innovación')->count());
    }
}
