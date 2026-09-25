<?php

namespace Tests\Feature\Regression;

use App\Models\City;
use App\Models\Department;
use App\Models\ResearchLine;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-20260921-064 — Líneas de Investigación no aparecía en Catálogos Simples.
 *
 * research-lines tiene rutas CRUD completas (Route::resource en routes/web.php)
 * con la misma forma que technological-lines, thematic-areas,
 * investigation-types y project-modalities (nombre + descripcion +
 * training_center_id), pero CatalogoController::simples() nunca la incluía
 * en la consulta ni en la vista admin.catalogos.simples.
 *
 * Este test verifica:
 * - La tarjeta "Líneas de Investigación" aparece en catálogos simples.
 * - Sin registros, muestra el estado vacío ("Sin registros").
 * - Un administrador_sistema puede crear una línea de investigación desde
 *   el modal de catálogos simples (_from_simples=1) y vuelve a esa página.
 * - La línea creada aparece listada en catálogos simples.
 */
class BUG20260921064Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(string $sufijo): TrainingCenter
    {
        $depto = Department::create(['nombre' => "Depto BUG-064{$sufijo}"]);
        $ciudad = City::create(['nombre' => "Ciudad BUG-064{$sufijo}", 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => "Centro BUG-064{$sufijo}", 'codigo' => "B064{$sufijo}", 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    private function crearAdmin(TrainingCenter $centro): User
    {
        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');
        $admin->givePermissionTo(['catalogos.leer', 'catalogos.crear']);

        return $admin;
    }

    public function test_catalogos_simples_muestra_la_tarjeta_lineas_de_investigacion(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('A');
        $admin = $this->crearAdmin($centro);

        $response = $this->actingAs($admin)->get(route('admin.catalogos.simples'));

        $response->assertOk();
        $response->assertSee('Líneas de Investigación');
    }

    public function test_sin_lineas_de_investigacion_muestra_estado_vacio(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('B');
        $admin = $this->crearAdmin($centro);

        $this->assertSame(0, ResearchLine::count());

        $response = $this->actingAs($admin)->get(route('admin.catalogos.simples'));

        $response->assertOk();
        $response->assertSee('Sin registros');
    }

    public function test_admin_puede_crear_una_linea_de_investigacion_desde_catalogos_simples(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('C');
        $admin = $this->crearAdmin($centro);

        $response = $this->actingAs($admin)->post(route('admin.research-lines.store'), [
            'nombre' => 'Inteligencia Artificial',
            'descripcion' => 'Creada desde el test',
            '_from_simples' => '1',
        ]);

        $response->assertRedirect(route('admin.catalogos.simples'));
        $this->assertDatabaseHas('research_lines', [
            'nombre' => 'Inteligencia Artificial',
            'training_center_id' => $centro->id,
        ]);
    }

    public function test_linea_de_investigacion_creada_aparece_en_catalogos_simples(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro('D');
        $admin = $this->crearAdmin($centro);

        ResearchLine::create([
            'nombre' => 'Biotecnología',
            'descripcion' => 'Línea de prueba',
            'training_center_id' => $centro->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.catalogos.simples'));

        $response->assertOk();
        $response->assertSee('Biotecnología');
    }
}
