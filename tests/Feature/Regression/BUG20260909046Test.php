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
 * BUG-20260909-046 — Un usuario con rol lider_proyecto pero sin proyecto
 * asignado (sin Project con lider_proyecto_user_id apuntando a él) accede
 * a /lider-proyecto y recibe un 404 genérico de Laravel sin layout ni
 * navegación de la app. El usuario queda atrapado sin forma de navegar
 * de regreso.
 *
 * Corrección: crear resources/views/errors/404.blade.php personalizada
 * que use el layout de la app y ofrezca un enlace de regreso. Además,
 * mejorar el mensaje en LiderProyectoContext::miProyecto() para ser más
 * descriptivo.
 */
class BUG20260909046Test extends TestCase
{
    use RefreshDatabase;

    private User $userSinProyecto;
    private TrainingCenter $centro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);

        $depto = Department::create(['nombre' => 'Depto BUG-046']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-046', 'department_id' => $depto->id]);
        $this->centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-046', 'codigo' => 'B046', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        // Usuario con rol lider_proyecto pero SIN proyecto asignado
        $this->userSinProyecto = User::factory()->create([
            'training_center_id' => $this->centro->id,
            'estado' => EstadoEnum::Activo
        ]);
        $this->userSinProyecto->assignRole('lider_proyecto');
    }

    public function test_lider_proyecto_sin_proyecto_recibe_404(): void
    {
        $response = $this->actingAs($this->userSinProyecto)
            ->get('/lider-proyecto');

        $response->assertStatus(404);
    }

    public function test_lider_proyecto_sin_proyecto_404_contiene_enlace_regreso(): void
    {
        $response = $this->actingAs($this->userSinProyecto)
            ->get('/lider-proyecto');

        $response->assertStatus(404);
        // La respuesta debe contener un enlace/botón para volver
        $response->assertSee('Volver al dashboard', false);
        $response->assertSee('dashboard', false);
    }

    public function test_lider_proyecto_sin_proyecto_404_contiene_mensaje_descriptivo(): void
    {
        $response = $this->actingAs($this->userSinProyecto)
            ->get('/lider-proyecto');

        $response->assertStatus(404);
        // Debe mencionar que no tiene proyecto asignado
        $response->assertSee('No tienes un proyecto asignado', false);
        // Debe sugerir contactar al Director de Semilleros
        $response->assertSee('Director de Semilleros', false);
    }

    public function test_lider_proyecto_sin_proyecto_404_tiene_layout_app(): void
    {
        $response = $this->actingAs($this->userSinProyecto)
            ->get('/lider-proyecto');

        $response->assertStatus(404);
        // La vista debe renderizar con el layout de la app (sidebar, navbar, etc)
        // Verificamos que NO es la página 404 desnuda de Laravel
        // (que no contendría estos elementos de la app)
        $response->assertSee('SIGESI', false);
    }
}
