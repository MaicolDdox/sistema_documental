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
 * Regresión: BUG-20260813-001
 * La limpieza final de la fase 2c del rediseño de roles (eliminación de
 * ResearchGroup/Product y de la tabla project_seedlings) dejó varias páginas
 * de lider_semillero rotas por referencias colgantes:
 * - components/app-layout.blade.php (sidebar, se renderiza en TODA página)
 *   llamaba a \App\Models\Product::whereIn(...) y a la tabla eliminada
 *   project_seedlings dentro del menú de lider_semillero — error fatal en
 *   cualquier página para ese rol.
 * - IntegrantesController::index() usaba DB::table('project_seedlings').
 * - InfoSemilleroController::edit() usaba ->with('researchGroup'), relación
 *   eliminada del modelo Seedling.
 * Corregido: 2026-08-13.
 *
 * Nota: el test sobre IntegrantesController se eliminó en BUG-20260813-014,
 * fecha en la que ese controlador/ruta/vista se retiraron por completo del
 * sistema (sin funcionalidad real en el rol lider_semillero).
 */
class BUG20260813001Test extends TestCase
{
    use RefreshDatabase;

    private function crearLiderSemilleroConSemillero(): User
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 901,
        ]);

        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);

        $lider = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $lider->assignRole('lider_semillero');

        \App\Models\Seedling::create([
            'creator_id' => $lider->id,
            'leader_id' => $lider->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 1001,
            'logo' => 'default.png',
            'estado' => EstadoEnum::Activo,
        ]);

        return $lider;
    }

    public function test_dashboard_lider_semillero_no_truena_por_sidebar(): void
    {
        $lider = $this->crearLiderSemilleroConSemillero();

        $response = $this->actingAs($lider)->get('/lider-semillero');

        $response->assertStatus(200);
    }

    public function test_info_semillero_no_usa_relacion_eliminada_research_group(): void
    {
        $lider = $this->crearLiderSemilleroConSemillero();

        $response = $this->actingAs($lider)->get(route('lider-sem.info-semillero'));

        $response->assertStatus(200);
    }

    public function test_app_layout_no_referencia_product_ni_project_seedlings_ni_research_group(): void
    {
        $contenido = file_get_contents(resource_path('views/components/app-layout.blade.php'));

        $this->assertStringNotContainsString('project_seedlings', $contenido);
        $this->assertStringNotContainsString('\App\Models\Product', $contenido);
        $this->assertStringNotContainsString('researchGroup', $contenido);
    }
}
