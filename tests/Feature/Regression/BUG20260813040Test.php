<?php

namespace Tests\Feature\Regression;

use App\Models\City;
use App\Models\Department;
use App\Models\LinkageType;
use App\Models\TrainingCenter;
use App\Models\User;
use Database\Seeders\LinkageTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-040
 * El desplegable "Tipo de Vinculación" del formulario de actualizar
 * perfil debe ofrecer únicamente: Planta, Contratista, Otros.
 */
class BUG20260813040Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(string $sufijo): TrainingCenter
    {
        $depto = Department::create(['nombre' => "Depto BUG-040{$sufijo}"]);
        $ciudad = City::create(['nombre' => "Ciudad BUG-040{$sufijo}", 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => "Centro BUG-040{$sufijo}", 'codigo' => "B040{$sufijo}", 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    public function test_seeder_deja_exactamente_planta_contratista_otros(): void
    {
        $this->crearCentro('A');

        (new LinkageTypesSeeder)->run();

        $this->assertSame(3, LinkageType::count());
        $this->assertDatabaseHas('linkage_types', ['nombre' => 'Planta']);
        $this->assertDatabaseHas('linkage_types', ['nombre' => 'Contratista']);
        $this->assertDatabaseHas('linkage_types', ['nombre' => 'Otros']);
    }

    public function test_formulario_de_perfil_muestra_las_3_opciones(): void
    {
        $centro = $this->crearCentro('B');
        (new LinkageTypesSeeder)->run();
        $user = User::factory()->create(['training_center_id' => $centro->id]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Planta');
        $response->assertSee('Contratista');
        $response->assertSee('Otros');
    }
}
