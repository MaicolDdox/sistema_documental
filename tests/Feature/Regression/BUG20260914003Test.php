<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\GrupoInvestigacion;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-20260914-003 — El método VinculacionGrupoInvestigacionDirectorController::index()
 * calculaba $directoresYaVinculadosIds a partir de TODOS los grupos de investigación
 * sin filtrar por training_center_id. Esto causaba fuga de datos: un administrador_sistema
 * en Centro A vería deshabilitados los directores de Centro B que ya estaban
 * vinculados a grupos en Centro B, revelando información sobre asignaciones
 * de otros centros y estructura organizativa ajena.
 *
 * Corrección: añadir ->where('training_center_id', $user->training_center_id)
 * a la query que calcula directoresYaVinculadosIds (línea 48 del controlador).
 */
class BUG20260914003Test extends TestCase
{
    use RefreshDatabase;

    private User $adminCentroA;

    private User $directorCentroB;

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

        // Administrador Sistema en Centro A (quien hace la consulta)
        $this->adminCentroA = User::factory()->create([
            'training_center_id' => $this->centroA->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $this->adminCentroA->assignRole('administrador_sistema');

        // Director de Grupo de Investigación en Centro B
        $this->directorCentroB = User::factory()->create([
            'training_center_id' => $this->centroB->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $this->directorCentroB->assignRole('director_grupo_investigacion');

        // Creador de Grupo en Centro B
        $creatorCentroB = User::factory()->create([
            'training_center_id' => $this->centroB->id,
            'estado' => EstadoEnum::Activo,
        ]);

        // Grupo de Investigación en Centro B con director vinculado
        GrupoInvestigacion::create([
            'creator_id' => $creatorCentroB->id,
            'director_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Grupo Investigación Centro B',
            'codigo' => 'GIB-1',
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);
    }

    public function test_director_de_otro_centro_no_aparece_en_lista_vinculados(): void
    {
        /**
         * El director de Centro B está vinculado a un grupo en Centro B.
         * El admin de Centro A NO debe verlo en directoresYaVinculadosIds.
         */
        $response = $this->actingAs($this->adminCentroA)
            ->get(route('admin.vinculaciones-grupo-investigacion.index'));

        $response->assertStatus(200);
        $directoresYaVinculadosIds = $response->viewData('directoresYaVinculadosIds');

        // El ID del director de Centro B NO debe estar en la lista de vinculados
        $this->assertNotContains(
            $this->directorCentroB->id,
            $directoresYaVinculadosIds,
            'El director de Centro B no debe aparecer en directoresYaVinculadosIds del Centro A'
        );
    }

    public function test_lista_vinculados_solo_contiene_directores_del_propio_centro(): void
    {
        // Crear un director en Centro A y vincularlo a un grupo
        $directorCentroA = User::factory()->create([
            'training_center_id' => $this->centroA->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $directorCentroA->assignRole('director_grupo_investigacion');

        $creatorCentroA = $this->adminCentroA;
        GrupoInvestigacion::create([
            'creator_id' => $creatorCentroA->id,
            'director_id' => $directorCentroA->id,
            'training_center_id' => $this->centroA->id,
            'nombre' => 'Grupo Investigación Centro A',
            'codigo' => 'GIA-1',
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($this->adminCentroA)
            ->get(route('admin.vinculaciones-grupo-investigacion.index'));

        $response->assertStatus(200);
        $directoresYaVinculadosIds = $response->viewData('directoresYaVinculadosIds');

        // Debe incluir al director de Centro A
        $this->assertContains(
            $directorCentroA->id,
            $directoresYaVinculadosIds,
            'El director de Centro A debe estar en directoresYaVinculadosIds'
        );

        // NO debe incluir al director de Centro B
        $this->assertNotContains(
            $this->directorCentroB->id,
            $directoresYaVinculadosIds,
            'El director de Centro B no debe aparecer'
        );
    }

    public function test_admin_ve_solo_grupos_de_su_centro(): void
    {
        /**
         * Verificación complementaria: el listado de grupos también debe
         * estar filtrado por training_center_id.
         */
        // Centro A es vacío (sin grupos)
        $response = $this->actingAs($this->adminCentroA)
            ->get(route('admin.vinculaciones-grupo-investigacion.index'));

        $response->assertStatus(200);
        $grupos = $response->viewData('grupos');

        // El Centro A no debe ver grupos del Centro B
        $this->assertTrue(
            $grupos->isEmpty(),
            'Admin de Centro A no debe ver grupos de Centro B'
        );
    }
}
