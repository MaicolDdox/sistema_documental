<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-20260914-002 — El método VinculacionSemilleroLiderController::index()
 * calculaba $lideresYaVinculadosIds a partir de TODOS los semilleros sin
 * filtrar por training_center_id. Esto causaba fuga de datos: un director_semilleros
 * en Centro A vería deshabilitados los líderes de Centro B que ya estaban
 * vinculados a semilleros en Centro B, revelando información sobre asignaciones
 * de otros centros.
 *
 * Corrección: añadir ->where('training_center_id', $user->training_center_id)
 * a la query que calcula lideresYaVinculadosIds (línea 48 del controlador).
 */
class BUG20260914002Test extends TestCase
{
    use RefreshDatabase;

    private User $directorCentroA;

    private User $liderCentroB;

    private TrainingCenter $centroA;

    private TrainingCenter $centroB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Crear Centro A
        $deptoA = Department::create(['nombre' => 'Depto Centro A']);
        $ciudadA = City::create(['nombre' => 'Ciudad Centro A', 'department_id' => $deptoA->id]);
        $this->centroA = TrainingCenter::create([
            'nombre' => 'Centro Formación A',
            'codigo' => 'CFMA',
            'activo' => true,
            'department_id' => $deptoA->id,
            'city_id' => $ciudadA->id,
        ]);

        // Crear Centro B
        $deptoB = Department::create(['nombre' => 'Depto Centro B']);
        $ciudadB = City::create(['nombre' => 'Ciudad Centro B', 'department_id' => $deptoB->id]);
        $this->centroB = TrainingCenter::create([
            'nombre' => 'Centro Formación B',
            'codigo' => 'CFMB',
            'activo' => true,
            'department_id' => $deptoB->id,
            'city_id' => $ciudadB->id,
        ]);

        // Director de Semilleros en Centro A (quien hace la consulta)
        $this->directorCentroA = User::factory()->create([
            'training_center_id' => $this->centroA->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $this->directorCentroA->assignRole('director_semilleros');

        // Líder de Semillero en Centro B
        $this->liderCentroB = User::factory()->create([
            'training_center_id' => $this->centroB->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $this->liderCentroB->assignRole('lider_semillero');

        // Creador en Centro B (para crear el semillero)
        $creatorCentroB = User::factory()->create([
            'training_center_id' => $this->centroB->id,
            'estado' => EstadoEnum::Activo,
        ]);

        // Semillero en Centro B con el lider de Centro B vinculado
        Seedling::create([
            'creator_id' => $creatorCentroB->id,
            'leader_id' => $this->liderCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Semillero Centro B con Líder Vinculado',
            'codigo' => 'SB-L',
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);
    }

    public function test_lider_de_otro_centro_no_aparece_en_lista_vinculados(): void
    {
        /**
         * El líder de Centro B está vinculado a un semillero en Centro B.
         * El director_semilleros de Centro A NO debe verlo en la lista de
         * lideresYaVinculadosIds, ya que no tiene semilleros en su centro.
         */
        $response = $this->actingAs($this->directorCentroA)
            ->get(route('dir-sem.vinculaciones.index'));

        $response->assertStatus(200);
        $lideresYaVinculadosIds = $response->viewData('lideresYaVinculadosIds');

        // El ID del líder de Centro B NO debe estar en la lista de vinculados
        $this->assertNotContains(
            $this->liderCentroB->id,
            $lideresYaVinculadosIds,
            'El líder de Centro B no debe aparecer en lideresYaVinculadosIds del Centro A'
        );
    }

    public function test_lista_vinculados_solo_contiene_lideres_del_propio_centro(): void
    {
        // Crear un líder en Centro A y vincularlo a un semillero
        $liderCentroA = User::factory()->create([
            'training_center_id' => $this->centroA->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $liderCentroA->assignRole('lider_semillero');

        $creatorCentroA = $this->directorCentroA;
        Seedling::create([
            'creator_id' => $creatorCentroA->id,
            'leader_id' => $liderCentroA->id,
            'training_center_id' => $this->centroA->id,
            'nombre' => 'Semillero Centro A',
            'codigo' => 'SA-1',
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($this->directorCentroA)
            ->get(route('dir-sem.vinculaciones.index'));

        $response->assertStatus(200);
        $lideresYaVinculadosIds = $response->viewData('lideresYaVinculadosIds');

        // Debe incluir al líder de Centro A
        $this->assertContains(
            $liderCentroA->id,
            $lideresYaVinculadosIds,
            'El líder de Centro A debe estar en lideresYaVinculadosIds'
        );

        // NO debe incluir al líder de Centro B
        $this->assertNotContains(
            $this->liderCentroB->id,
            $lideresYaVinculadosIds,
            'El líder de Centro B no debe aparecer'
        );
    }
}
