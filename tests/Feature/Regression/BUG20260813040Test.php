<?php

namespace Tests\Feature\Regression;

use App\Models\LinkageType;
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

    public function test_seeder_deja_exactamente_planta_contratista_otros(): void
    {
        (new LinkageTypesSeeder)->run();

        $this->assertSame(3, LinkageType::count());
        $this->assertDatabaseHas('linkage_types', ['nombre' => 'Planta']);
        $this->assertDatabaseHas('linkage_types', ['nombre' => 'Contratista']);
        $this->assertDatabaseHas('linkage_types', ['nombre' => 'Otros']);
    }

    public function test_formulario_de_perfil_muestra_las_3_opciones(): void
    {
        (new LinkageTypesSeeder)->run();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('Planta');
        $response->assertSee('Contratista');
        $response->assertSee('Otros');
    }
}
