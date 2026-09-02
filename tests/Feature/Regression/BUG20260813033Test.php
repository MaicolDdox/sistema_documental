<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\TrainingCenter;
use App\Models\User;
use App\Support\UserOwnershipAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-033
 * created_by_user_id se agregó sin backfill: los líderes de semillero
 * creados antes de esa migración quedaban con ese campo en null para
 * siempre, y UserOwnershipAccess::canManage() los bloqueaba — su propio
 * director de semilleros ya no los podía gestionar (activar/desactivar,
 * editar, eliminar).
 */
class BUG20260813033Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(): TrainingCenter
    {
        $depto = \App\Models\Department::firstOrCreate(['nombre' => 'Depto Test BUG-033']);
        $ciudad = \App\Models\City::firstOrCreate(['nombre' => 'Ciudad Test BUG-033', 'department_id' => $depto->id]);

        return TrainingCenter::create([
            'nombre' => 'Centro Test BUG-033', 'codigo' => 'BUG033', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);
    }

    public function test_canManage_permite_cuenta_huerfana_sin_creador_registrado(): void
    {
        $director = User::factory()->create(['created_by_user_id' => null]);
        $liderHuerfano = User::factory()->create(['created_by_user_id' => null]);

        $this->assertTrue(UserOwnershipAccess::canManage($director, $liderHuerfano));
    }

    public function test_canManage_sigue_bloqueando_cuenta_creada_por_otro(): void
    {
        $creadorReal = User::factory()->create();
        $otroDirector = User::factory()->create();
        $lider = User::factory()->create(['created_by_user_id' => $creadorReal->id]);

        $this->assertFalse(UserOwnershipAccess::canManage($otroDirector, $lider));
        $this->assertTrue(UserOwnershipAccess::canManage($creadorReal, $lider));
    }

    public function test_director_puede_activar_desactivar_lider_de_semillero_legacy_sin_creador(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centro = $this->crearCentro();

        $director = User::factory()->create(['training_center_id' => $centro->id, 'created_by_user_id' => null]);
        $director->assignRole('director_semilleros');

        // Simula un líder creado ANTES de que existiera created_by_user_id.
        $liderLegacy = User::factory()->create([
            'training_center_id' => $centro->id,
            'created_by_user_id' => null,
            'estado' => EstadoEnum::Activo,
        ]);
        $liderLegacy->assignRole('lider_semillero');

        $response = $this->actingAs($director)->post(route('dir-sem.lideres.toggle-estado', $liderLegacy));

        $response->assertRedirect(route('dir-sem.lideres.index'));
        $this->assertSame(EstadoEnum::Inactivo, $liderLegacy->fresh()->estado);
    }

    public function test_director_no_puede_gestionar_lider_de_otro_centro_aunque_sea_huerfano(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $centroPropio = $this->crearCentro();
        $centroAjeno = TrainingCenter::create([
            'nombre' => 'Centro Ajeno BUG-033', 'codigo' => 'BUG033-B', 'activo' => true,
            'department_id' => $centroPropio->department_id, 'city_id' => $centroPropio->city_id,
        ]);

        $director = User::factory()->create(['training_center_id' => $centroPropio->id, 'created_by_user_id' => null]);
        $director->assignRole('director_semilleros');

        $liderDeOtroCentro = User::factory()->create([
            'training_center_id' => $centroAjeno->id,
            'created_by_user_id' => null,
            'estado' => EstadoEnum::Activo,
        ]);
        $liderDeOtroCentro->assignRole('lider_semillero');

        $this->actingAs($director)
            ->post(route('dir-sem.lideres.toggle-estado', $liderDeOtroCentro))
            ->assertForbidden();
    }
}
