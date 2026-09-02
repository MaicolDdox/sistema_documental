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
 * BUG-20260813-052 — FEAT-20260830-001 (Fase 2): aislamiento estricto por
 * rol activo. Antes de esta fase, un usuario con 2+ roles ya podía entrar
 * por URL directa a los módulos de CUALQUIERA de sus roles asignados
 * (Spatie `role:` solo valida que el rol esté asignado, sin importar cuál
 * esté "activo"). Ahora cada módulo por rol exige además que ese rol sea
 * el activo en sesión (middleware `active_role:`), devolviendo 403 si no.
 */
class BUG20260813052Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $depto = Department::create(['nombre' => 'Depto BUG-052']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-052', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-052', 'codigo' => 'B052', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $this->user = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $this->user->assignRole('lider_semillero');
        $this->user->assignRole('co_investigador');
    }

    public function test_modulo_del_rol_no_activo_devuelve_403_por_url_directa(): void
    {
        // lider_semillero es el principal (mayor prioridad) -> queda activo
        // por defecto. co_investigador es un rol asignado pero NO activo.
        $response = $this->actingAs($this->user)->get('/co-investigador');

        $response->assertForbidden();
    }

    public function test_modulo_del_rol_activo_sigue_funcionando(): void
    {
        $response = $this->actingAs($this->user)->get(route('lider-sem.dashboard'));

        $response->assertOk();
    }

    public function test_tras_cambiar_de_rol_el_modulo_antes_bloqueado_queda_accesible_y_el_anterior_se_bloquea(): void
    {
        $this->actingAs($this->user)->get(route('lider-sem.dashboard'))->assertOk();

        $this->post(route('roles.switch'), ['role' => 'co_investigador'])
            ->assertRedirect('/co-investigador');

        // Ahora co_investigador es el activo: su módulo responde...
        $this->get('/co-investigador')->assertOk();

        // ...y el que antes era accesible (lider_semillero) ahora se bloquea.
        $this->get(route('lider-sem.dashboard'))->assertForbidden();
    }

    public function test_un_solo_rol_asignado_nunca_recibe_403_por_aislamiento(): void
    {
        $solo = User::factory()->create(['estado' => EstadoEnum::Activo]);
        $solo->assignRole('co_investigador');

        $response = $this->actingAs($solo)->get('/co-investigador');

        $response->assertOk();
    }
}
