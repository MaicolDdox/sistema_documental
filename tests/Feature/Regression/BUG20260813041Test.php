<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-041
 * "Áreas del Conocimiento" (y "Grandes Áreas de Conocimiento") se eliminó
 * por completo del sistema: rutas, controladores, vistas, modelos, seeder,
 * enlaces del sidebar y las 2 tablas de BD (ambas estaban vacías).
 */
class BUG20260813041Test extends TestCase
{
    use RefreshDatabase;

    public function test_rutas_de_areas_de_conocimiento_ya_no_existen(): void
    {
        $this->assertFalse(Route::has('admin.knowledge-areas.index'));
        $this->assertFalse(Route::has('admin.knowledge-grand-areas.index'));
    }

    public function test_tablas_de_areas_de_conocimiento_ya_no_existen(): void
    {
        $this->assertFalse(Schema::hasTable('knowledge_areas'));
        $this->assertFalse(Schema::hasTable('knowledge_grand_areas'));
    }

    public function test_modelos_ya_no_existen(): void
    {
        $this->assertFalse(class_exists(\App\Models\KnowledgeArea::class));
        $this->assertFalse(class_exists(\App\Models\KnowledgeGrandArea::class));
    }

    public function test_sidebar_admin_no_muestra_ningun_enlace_de_areas_de_conocimiento(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-041']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-041', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro Test BUG-041', 'codigo' => 'BUG041', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Áreas del Conocimiento');
        $response->assertDontSee('Áreas Conoc.');
        $response->assertDontSee('G. Áreas Conoc.');
    }
}
