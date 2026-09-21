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
 * BUG-20260914-001 — El método SemilleroController::siguienteCodigoSugerido()
 * sugería el siguiente código basándose en el máximo global de todos los
 * semilleros, sin filtrar por training_center_id. Esto causaba fuga de datos:
 * si Centro B tenía semilleros con códigos altos (ej. "50"), un director_semilleros
 * en Centro A sin semilleros vería sugerido el código "51", revelando información
 * sobre el máximo de otro centro.
 *
 * Corrección: filtrar por training_center_id en la query del método
 * siguienteCodigoSugerido() (línea 336 del controlador).
 */
class BUG20260914001Test extends TestCase
{
    use RefreshDatabase;

    private User $directorCentroA;

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

        // Director de Semilleros en Centro A
        $this->directorCentroA = User::factory()->create([
            'training_center_id' => $this->centroA->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $this->directorCentroA->assignRole('director_semilleros');

        // Crear varios semilleros en Centro B con códigos altos
        $creatorCentroB = User::factory()->create([
            'training_center_id' => $this->centroB->id,
            'estado' => EstadoEnum::Activo,
        ]);
        for ($i = 48; $i <= 52; $i++) {
            Seedling::create([
                'creator_id' => $creatorCentroB->id,
                'training_center_id' => $this->centroB->id,
                'nombre' => "Semillero Centro B - $i",
                'codigo' => (string) $i,
                'logo' => '',
                'estado' => EstadoEnum::Activo,
            ]);
        }
    }

    public function test_codigo_sugerido_no_revela_maximo_global(): void
    {
        /**
         * El Centro A NO tiene semilleros.
         * El Centro B tiene 5 semilleros con códigos 48-52.
         * Cuando director_semilleros de Centro A solicita create,
         * el código sugerido debe ser "1", no "53".
         */
        $response = $this->actingAs($this->directorCentroA)
            ->get(route('dir-sem.semilleros.create'));

        $response->assertStatus(200);
        $siguienteCodigo = $response->viewData('siguienteCodigo');

        // Verificar que el código sugerido es bajo (máximo de Centro A + 1)
        // Centro A tiene 0 semilleros, por lo que debe ser "1"
        $this->assertEquals('1', $siguienteCodigo);
    }

    public function test_codigo_sugerido_se_basa_en_centro_del_usuario(): void
    {
        // Crear 3 semilleros en Centro A con códigos bajos
        $creatorCentroA = $this->directorCentroA;
        for ($i = 10; $i <= 12; $i++) {
            Seedling::create([
                'creator_id' => $creatorCentroA->id,
                'training_center_id' => $this->centroA->id,
                'nombre' => "Semillero Centro A - $i",
                'codigo' => (string) $i,
                'logo' => '',
                'estado' => EstadoEnum::Activo,
            ]);
        }

        $response = $this->actingAs($this->directorCentroA)
            ->get(route('dir-sem.semilleros.create'));

        $response->assertStatus(200);
        $siguienteCodigo = $response->viewData('siguienteCodigo');

        // Debe ser "13" (máximo 12 + 1 en Centro A), no "53" (máximo global)
        $this->assertEquals('13', $siguienteCodigo);
    }

    public function test_codigo_sugerido_en_index_tampoco_revela_datos_otros_centros(): void
    {
        /**
         * El listado index() también sugiere el siguiente código.
         * Verificar que tampoco revela información de otros centros.
         */
        $response = $this->actingAs($this->directorCentroA)
            ->get(route('dir-sem.semilleros.index'));

        $response->assertStatus(200);
        $siguienteCodigo = $response->viewData('siguienteCodigo');

        // Centro A sin semilleros, debe sugerir "1", no "53"
        $this->assertEquals('1', $siguienteCodigo);
    }
}
