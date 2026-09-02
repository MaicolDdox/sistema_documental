<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\InvestigationType;
use App\Models\Project;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-20260813-058 — un usuario con lider_proyecto como rol SECUNDARIO
 * (multi-rol) nunca puede aparecer en el listado de "líderes de proyecto
 * asignables" de ningún líder de semillero, porque ese listado filtraba
 * solo por created_by_user_id (quién creó la cuenta) — y nadie puede ser
 * su propio creador. El fix amplía el filtro para incluir también a los
 * lider_proyecto del mismo centro de formación, SIN quitar el filtro por
 * creador existente (nada de lo que ya funcionaba deja de funcionar).
 */
class BUG20260813058Test extends TestCase
{
    use RefreshDatabase;

    private TrainingCenter $centro;
    private TrainingCenter $otroCentro;
    private User $liderSemillero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto BUG-058']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-058', 'department_id' => $depto->id]);
        $this->centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-058', 'codigo' => 'B058', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
        $this->otroCentro = TrainingCenter::create([
            'nombre' => 'Otro Centro BUG-058', 'codigo' => 'B058B', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $director = User::factory()->create(['training_center_id' => $this->centro->id, 'estado' => EstadoEnum::Activo]);
        $director->assignRole('director_semilleros');

        $this->liderSemillero = User::factory()->create(['training_center_id' => $this->centro->id, 'estado' => EstadoEnum::Activo]);
        $this->liderSemillero->assignRole('lider_semillero');

        $semillero = Seedling::create([
            'creator_id' => $director->id,
            'leader_id' => $this->liderSemillero->id,
            'training_center_id' => $this->centro->id,
            'nombre' => 'Semillero BUG-058', 'codigo' => 'S058', 'logo' => '', 'estado' => EstadoEnum::Activo,
        ]);
        $this->semillero = $semillero;
    }

    private Seedling $semillero;

    private function datosProyecto(int $liderProyectoUserId): array
    {
        return [
            'nombre' => 'Proyecto BUG-058',
            'lider_proyecto_user_id' => $liderProyectoUserId,
            'research_line_id' => ResearchLine::firstOrCreate(['nombre' => 'Linea BUG-058'])->id,
            'technological_line_id' => TechnologicalLine::firstOrCreate(['nombre' => 'Linea Tec BUG-058'])->id,
            'thematic_area_id' => ThematicArea::firstOrCreate(['nombre' => 'Area BUG-058'])->id,
            'project_modality_id' => ProjectModality::firstOrCreate(['nombre' => 'Modalidad BUG-058'])->id,
            'investigation_type_id' => InvestigationType::firstOrCreate(['nombre' => 'Tipo BUG-058'])->id,
        ];
    }

    public function test_comportamiento_viejo_sigue_funcionando_lider_proyecto_creado_por_mi(): void
    {
        $liderProyecto = User::factory()->create([
            'training_center_id' => $this->centro->id,
            'created_by_user_id' => $this->liderSemillero->id,
        ]);
        $liderProyecto->assignRole('lider_proyecto');

        $response = $this->actingAs($this->liderSemillero)
            ->post(route('lider-sem.proyectos.store'), $this->datosProyecto($liderProyecto->id));

        $response->assertSessionDoesntHaveErrors('lider_proyecto_user_id');
        $this->assertDatabaseHas('projects', ['lider_proyecto_user_id' => $liderProyecto->id]);
    }

    public function test_lider_proyecto_del_mismo_centro_sin_ser_creado_por_mi_ahora_si_se_puede_asignar(): void
    {
        $otroAdmin = User::factory()->create(['training_center_id' => $this->centro->id]);
        $otroAdmin->assignRole('administrador_sistema');

        // Creado por OTRO usuario (no por el líder de semillero que asigna),
        // pero del MISMO centro — este es exactamente el caso de los
        // usuarios multi-rol (33, 32) descrito por el usuario.
        $liderProyecto = User::factory()->create([
            'training_center_id' => $this->centro->id,
            'created_by_user_id' => $otroAdmin->id,
        ]);
        $liderProyecto->assignRole('lider_proyecto');

        $response = $this->actingAs($this->liderSemillero)
            ->post(route('lider-sem.proyectos.store'), $this->datosProyecto($liderProyecto->id));

        $response->assertSessionDoesntHaveErrors('lider_proyecto_user_id');
        $this->assertDatabaseHas('projects', ['lider_proyecto_user_id' => $liderProyecto->id]);
    }

    public function test_lider_proyecto_de_otro_centro_sigue_sin_poder_asignarse(): void
    {
        $liderProyectoOtroCentro = User::factory()->create([
            'training_center_id' => $this->otroCentro->id,
        ]);
        $liderProyectoOtroCentro->assignRole('lider_proyecto');

        $response = $this->actingAs($this->liderSemillero)
            ->post(route('lider-sem.proyectos.store'), $this->datosProyecto($liderProyectoOtroCentro->id));

        $response->assertSessionHasErrors('lider_proyecto_user_id');
        $this->assertDatabaseMissing('projects', ['lider_proyecto_user_id' => $liderProyectoOtroCentro->id]);
    }

    public function test_dropdown_del_formulario_incluye_a_los_del_mismo_centro(): void
    {
        $otroAdmin = User::factory()->create(['training_center_id' => $this->centro->id]);
        $otroAdmin->assignRole('administrador_sistema');

        $liderProyecto = User::factory()->create([
            'training_center_id' => $this->centro->id,
            'created_by_user_id' => $otroAdmin->id,
        ]);
        $liderProyecto->assignRole('lider_proyecto');
        \App\Models\Person::create([
            'user_id' => $liderProyecto->id,
            'primer_nombre' => 'CandidatoBug058', 'primer_apellido' => 'Test',
            'genero' => 'prefiero no decirlo', 'celular' => 0, 'eps' => '',
            'email_institucional' => 'candidato-bug058@test.com',
        ]);

        $response = $this->actingAs($this->liderSemillero)->get(route('lider-sem.proyectos.create'));

        $response->assertOk();
        $response->assertSee('CandidatoBug058');
    }
}
