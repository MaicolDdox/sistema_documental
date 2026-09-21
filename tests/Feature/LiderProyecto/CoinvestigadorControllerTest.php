<?php

namespace Tests\Feature\LiderProyecto;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reforma GDI/SDI: co_investigador_sdi (a diferencia del co_investigador
 * original, sin centro) ahora lleva training_center_id. El listado de
 * "disponibles para vincular" de LiderProyecto\CoinvestigadorController debe
 * mostrar solo los del MISMO centro que el lider_proyecto autenticado.
 */
class CoinvestigadorControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(string $sufijo): TrainingCenter
    {
        $depto = Department::create(['nombre' => "Depto LP-{$sufijo}"]);
        $ciudad = City::create(['nombre' => "Ciudad LP-{$sufijo}", 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => "Centro LP-{$sufijo}", 'codigo' => "LP-{$sufijo}", 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    private function crearLiderProyectoConProyecto(TrainingCenter $centro): array
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $liderProyecto->assignRole('lider_proyecto');

        $semillero = Seedling::create([
            'creator_id' => $liderProyecto->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero LP',
            'codigo' => (string) random_int(1000, 999999),
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $proyecto = Project::create([
            'project_creator_id' => $liderProyecto->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => ResearchLine::firstOrCreate(['training_center_id' => $centro->id, 'nombre' => 'Linea LP'])->id,
            'nombre' => 'Proyecto LP',
            'fecha_inicio' => now(),
            'estado' => 'activo',
        ]);

        return [$liderProyecto, $proyecto];
    }

    public function test_disponibles_solo_incluye_co_investigador_sdi_del_mismo_centro(): void
    {
        $centro = $this->crearCentro('A');
        $otroCentro = $this->crearCentro('B');
        [$liderProyecto] = $this->crearLiderProyectoConProyecto($centro);

        $mismoCentro = User::factory()->create(['training_center_id' => $centro->id, 'email' => 'mismo-centro@test.com']);
        $mismoCentro->assignRole('co_investigador_sdi');

        $otroCentroUser = User::factory()->create(['training_center_id' => $otroCentro->id, 'email' => 'otro-centro@test.com']);
        $otroCentroUser->assignRole('co_investigador_sdi');

        $response = $this->actingAs($liderProyecto)->get(route('lider-proyecto.coinvestigadores.index'));

        $response->assertOk();
        $response->assertViewHas('disponibles', function ($disponibles) use ($mismoCentro, $otroCentroUser) {
            return $disponibles->contains('id', $mismoCentro->id)
                && ! $disponibles->contains('id', $otroCentroUser->id);
        });
    }

    public function test_no_puede_vincular_un_co_investigador_sdi_de_otro_centro(): void
    {
        $centro = $this->crearCentro('C');
        $otroCentro = $this->crearCentro('D');
        [$liderProyecto, $proyecto] = $this->crearLiderProyectoConProyecto($centro);

        $otroCentroUser = User::factory()->create(['training_center_id' => $otroCentro->id]);
        $otroCentroUser->assignRole('co_investigador_sdi');

        // El filtro de "disponibles" ya lo excluye del listado; store()
        // también revalida el centro por si se envía el id directo (evita
        // vincular por POST directo a un co-investigador de otro centro).
        $response = $this->actingAs($liderProyecto)->post(route('lider-proyecto.coinvestigadores.store'), [
            'user_id' => $otroCentroUser->id,
        ]);

        $response->assertRedirect(route('lider-proyecto.coinvestigadores.index'));
        $this->assertDatabaseMissing('project_authors', [
            'project_id' => $proyecto->id,
            'user_id' => $otroCentroUser->id,
        ]);
    }
}
