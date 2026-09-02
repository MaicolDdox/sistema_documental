<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-014
 * Ajustes al rol Líder de Semillero:
 * 1. Eliminada la card "Acciones Rápidas" del dashboard — el dashboard solo
 *    es un resumen del semillero. Eliminada también la visualización del
 *    logo del semillero en TODAS las vistas del sistema (ya no hay forma
 *    de subirlo desde ningún rol — ver BUG-20260813-009).
 * 2. Eliminada la visualización del logo en "Info del Semillero".
 * 3. Eliminado por completo el ítem "Integrantes" del sidebar (ruta,
 *    controlador, vista) — no tenía funcionalidad real en este rol, operaba
 *    sobre Seedling::members(), un concepto de "integrantes" ya obsoleto.
 * 4. "Líderes de Proyecto" no se tocó.
 * Corregido: 2026-08-13.
 */
class BUG20260813014Test extends TestCase
{
    use RefreshDatabase;

    private function crearLiderConSemillero(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 915,
        ]);

        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);

        $lider = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $lider->assignRole('lider_semillero');

        $semillero = Seedling::create([
            'creator_id' => $lider->id,
            'leader_id' => $lider->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 2101,
            'logo' => 'algun-logo.png',
            'estado' => EstadoEnum::Activo,
        ]);

        return [$lider, $semillero];
    }

    public function test_dashboard_no_muestra_acciones_rapidas(): void
    {
        [$lider] = $this->crearLiderConSemillero();

        $response = $this->actingAs($lider)->get(route('lider-sem.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Acciones Rápidas');
    }

    public function test_dashboard_no_muestra_logo_del_semillero(): void
    {
        [$lider, $semillero] = $this->crearLiderConSemillero();

        $response = $this->actingAs($lider)->get(route('lider-sem.dashboard'));

        $response->assertOk();
        $response->assertDontSee($semillero->logo);
    }

    public function test_info_semillero_no_muestra_logo(): void
    {
        [$lider, $semillero] = $this->crearLiderConSemillero();

        $response = $this->actingAs($lider)->get(route('lider-sem.info-semillero'));

        $response->assertOk();
        $response->assertDontSee('>Logo<', false);
        $response->assertDontSee($semillero->logo);
    }

    public function test_ruta_y_sidebar_de_integrantes_ya_no_existen(): void
    {
        [$lider] = $this->crearLiderConSemillero();

        $this->assertFalse(Route::has('lider-sem.integrantes'));

        $response = $this->actingAs($lider)->get(route('lider-sem.dashboard'));

        $response->assertOk();
        // La card KPI "Integrantes" y la sección "Integrantes sin proyecto
        // activo" del dashboard sí deben seguir existiendo — solo se quitó
        // el ítem de sidebar que enlazaba a la ruta lider-sem.integrantes.
        $this->assertStringNotContainsString('lider-semillero/integrantes', $response->getContent());
    }

    public function test_lideres_de_proyecto_sigue_funcionando(): void
    {
        [$lider] = $this->crearLiderConSemillero();

        $response = $this->actingAs($lider)->get(route('lider-sem.dashboard'));

        $response->assertOk();
        $response->assertSee('Líderes de Proyecto');
    }
}
