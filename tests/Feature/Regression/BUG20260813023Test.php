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
 * Regresión: BUG-20260813-023
 * El dashboard del Director de Semilleros tenía 2 cards con gráficos
 * (Chart.js) sin ninguna función real: "Resumen gráfico del módulo" y
 * "Estado de semilleros". Se quitaron ambas cards, el <script> de Chart.js
 * asociado (@push('scripts')) y las variables de datos de gráfico ahora
 * muertas en el controlador (chartResumenLabels/Series,
 * chartEstadoSemillerosLabels/Series).
 */
class BUG20260813023Test extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_director_semilleros_no_muestra_cards_de_graficos(): void
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 930,
        ]);

        Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        $director = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $director->assignRole('director_semilleros');

        $response = $this->actingAs($director)->get(route('dir-sem.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Resumen gráfico del módulo');
        $response->assertDontSee('Estado de semilleros');
        $response->assertDontSee('chart-dir-sem-resumen', false);
        $response->assertDontSee('chart-dir-sem-estado', false);
    }
}
