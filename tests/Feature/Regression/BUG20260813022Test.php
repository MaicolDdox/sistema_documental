<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-022
 * El dashboard del Super Administrador tenía una card "Accesos rápidos"
 * (vincular centro con administrador, panel admin, gestión de usuarios,
 * centros de formación, datos paramétricos) — el dashboard debe ser
 * únicamente un resumen, sin atajos de navegación. Se quitó la card y se
 * aplanó el grid de 3 columnas a una sola columna (era la única card de la
 * columna derecha).
 */
class BUG20260813022Test extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_super_admin_no_muestra_accesos_rapidos(): void
    {
        Role::firstOrCreate(['name' => 'super_administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $superAdmin->assignRole('super_administrador');

        $response = $this->actingAs($superAdmin)->get(route('super-admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Accesos rápidos');
        $response->assertDontSee('Vincular centro con administrador');
    }
}
