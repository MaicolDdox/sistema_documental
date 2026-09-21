<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * BUG-20260813-062 — 12 de los 13 recursos de "Datos Paramétricos" en el
 * grupo de rutas admin.* (departments, cities, entity-positions,
 * linkage-types, training-program-types, training-programs, research-lines,
 * technological-lines, thematic-areas, project-modalities,
 * investigation-types, minciencias-typologies, minciencias-subcategories)
 * solo exigían auth + estado activo, sin middleware de rol. Cualquier
 * usuario autenticado de cualquiera de los 6 roles podía gestionarlos
 * conociendo la URL, aunque el sidebar solo mostrara el enlace a
 * super_administrador|administrador_sistema. Solo training-centers tenía
 * el middleware de rol correcto.
 */
class BUG20260813062Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(string $sufijo): TrainingCenter
    {
        $depto = Department::create(['nombre' => "Depto BUG-062{$sufijo}"]);
        $ciudad = City::create(['nombre' => "Ciudad BUG-062{$sufijo}", 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => "Centro BUG-062{$sufijo}", 'codigo' => "B062{$sufijo}", 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    public static function rutasCatalogoProvider(): array
    {
        return [
            ['admin.departments.index'],
            ['admin.cities.index'],
            ['admin.entity-positions.index'],
            ['admin.linkage-types.index'],
            ['admin.training-program-types.index'],
            ['admin.training-programs.index'],
            ['admin.research-lines.index'],
            ['admin.technological-lines.index'],
            ['admin.thematic-areas.index'],
            ['admin.project-modalities.index'],
            ['admin.investigation-types.index'],
            ['admin.minciencias-typologies.index'],
            ['admin.minciencias-subcategories.index'],
        ];
    }

    /**
     * @dataProvider rutasCatalogoProvider
     */
    public function test_un_rol_sin_privilegios_administrativos_recibe_403_en_catalogos_compartidos(string $routeName): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $centro = $this->crearCentro('A');

        $liderProyecto = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $liderProyecto->assignRole('lider_proyecto');

        $response = $this->actingAs($liderProyecto)->get(route($routeName));

        $response->assertForbidden();
    }

    public function test_administrador_sistema_si_puede_acceder_a_los_catalogos(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $centro = $this->crearCentro('B');

        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $response = $this->actingAs($admin)->get(route('admin.departments.index'));

        $response->assertOk();
    }

    /**
     * Reforma GDI/SDI: co_investigador (el rol global "sin centro fijo" que
     * motivó el nombre original de este test) fue eliminado — co_investigador_gdi
     * SÍ lleva training_center_id ahora. Se conserva la aserción que sigue
     * vigente: un co-investigador (sin privilegios administrativos) recibe
     * 403 en catálogos compartidos, sin importar que ahora tenga centro.
     */
    public function test_co_investigador_sin_privilegios_administrativos_recibe_403_en_catalogos(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $centro = $this->crearCentro('C');
        $coInvestigador = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $coInvestigador->assignRole('co_investigador_gdi');

        $response = $this->actingAs($coInvestigador)->get(route('admin.research-lines.index'));

        $response->assertForbidden();
    }
}
