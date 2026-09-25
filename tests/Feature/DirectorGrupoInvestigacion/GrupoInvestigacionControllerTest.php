<?php

namespace Tests\Feature\DirectorGrupoInvestigacion;

use App\Models\City;
use App\Models\Department;
use App\Models\GrupoInvestigacion;
use App\Models\ResearchLine;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-20260922-065 — Formulario "Información del grupo" (director_grupo_investigacion):
 * se elimina el campo "Logo (URL)" y la línea de investigación pasa de un
 * <select> de una sola opción (linea_investigacion_principal_id) a checkboxes
 * que permiten marcar varias líneas a la vez, vía el pivote nuevo
 * grupo_investigacion_research_lines.
 */
class GrupoInvestigacionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearDirectorConGrupo(): array
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto DGI']);
        $ciudad = City::create(['nombre' => 'Ciudad DGI', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro DGI', 'codigo' => 'DGI-001', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $director = User::factory()->create(['training_center_id' => $centro->id]);
        $director->assignRole('director_grupo_investigacion');

        $grupo = GrupoInvestigacion::create([
            'training_center_id' => $centro->id,
            'creator_id' => $director->id,
            'director_id' => $director->id,
            'nombre' => 'Grupo DGI',
            'codigo' => 'GDGI001',
            'estado' => 'activo',
        ]);

        $lineas = collect(['Inteligencia Artificial', 'Biotecnología', 'Energías Renovables'])
            ->map(fn ($nombre) => ResearchLine::create([
                'training_center_id' => $centro->id,
                'nombre' => $nombre,
                'descripcion' => 'Línea de prueba',
            ]));

        return [$director, $grupo, $lineas];
    }

    public function test_formulario_no_tiene_campo_logo(): void
    {
        [$director] = $this->crearDirectorConGrupo();

        $response = $this->actingAs($director)->get(route('director-grupo-investigacion.grupo.edit'));

        $response->assertOk();
        $response->assertDontSee('name="logo"', false);
        $response->assertDontSee('Logo (URL)');
    }

    public function test_formulario_muestra_checkboxes_de_lineas_de_investigacion(): void
    {
        [$director, , $lineas] = $this->crearDirectorConGrupo();

        $response = $this->actingAs($director)->get(route('director-grupo-investigacion.grupo.edit'));

        $response->assertOk();
        $response->assertSee('name="lineas_investigacion[]"', false);
        foreach ($lineas as $linea) {
            $response->assertSee($linea->nombre);
        }
    }

    public function test_director_puede_marcar_varias_lineas_de_investigacion(): void
    {
        [$director, $grupo, $lineas] = $this->crearDirectorConGrupo();

        $seleccionadas = $lineas->take(2)->pluck('id')->all();

        $response = $this->actingAs($director)->put(route('director-grupo-investigacion.grupo.update'), [
            'descripcion' => 'Grupo enfocado en IA y biotecnología',
            'lineas_investigacion' => $seleccionadas,
        ]);

        $response->assertRedirect(route('director-grupo-investigacion.grupo.edit'));

        $grupo->refresh();
        $this->assertSame('Grupo enfocado en IA y biotecnología', $grupo->descripcion);
        $this->assertEqualsCanonicalizing($seleccionadas, $grupo->lineasInvestigacion->pluck('id')->all());
    }

    public function test_director_puede_desmarcar_lineas_previamente_seleccionadas(): void
    {
        [$director, $grupo, $lineas] = $this->crearDirectorConGrupo();
        $grupo->lineasInvestigacion()->sync($lineas->pluck('id')->all());

        $this->actingAs($director)->put(route('director-grupo-investigacion.grupo.update'), [
            'descripcion' => $grupo->descripcion,
            'lineas_investigacion' => [],
        ]);

        $grupo->refresh();
        $this->assertCount(0, $grupo->lineasInvestigacion);
    }

    public function test_no_acepta_lineas_de_investigacion_de_otro_centro(): void
    {
        [$director, $grupo] = $this->crearDirectorConGrupo();

        $otroCentro = TrainingCenter::create([
            'nombre' => 'Otro Centro DGI', 'codigo' => 'DGI-002', 'activo' => true,
            'department_id' => $grupo->trainingCenter->department_id,
            'city_id' => $grupo->trainingCenter->city_id,
        ]);
        $lineaAjena = ResearchLine::create([
            'training_center_id' => $otroCentro->id,
            'nombre' => 'Línea Ajena',
            'descripcion' => 'No pertenece al centro del director',
        ]);

        $response = $this->actingAs($director)->put(route('director-grupo-investigacion.grupo.update'), [
            'lineas_investigacion' => [$lineaAjena->id],
        ]);

        $response->assertSessionHasErrors('lineas_investigacion.0');
        $grupo->refresh();
        $this->assertCount(0, $grupo->lineasInvestigacion);
    }
}
