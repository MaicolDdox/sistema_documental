<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-045
 * El campo "Código" del formulario de crear semillero ahora acepta
 * alfanumérico (ej: "ADSO2026"), no solo números. seedlings.codigo pasó de
 * integer a varchar(50).
 */
class BUG20260813045Test extends TestCase
{
    use RefreshDatabase;

    private function crearDirectorConCentro(): User
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-045']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-045', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id, 'city_id' => $ciudad->id,
            'nombre' => 'Centro Test BUG-045', 'codigo' => 'CT045',
        ]);

        Permission::firstOrCreate(['name' => 'semilleros.crear', 'guard_name' => 'web']);
        $rol = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rol->givePermissionTo('semilleros.crear');

        $director = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $director->assignRole('director_semilleros');

        return $director;
    }

    public function test_codigo_alfanumerico_se_guarda_correctamente(): void
    {
        $director = $this->crearDirectorConCentro();

        $response = $this->actingAs($director)->post(route('dir-sem.semilleros.store'), [
            'nombre' => 'Semillero ADSO',
            'codigo' => 'ADSO2026',
        ]);

        $response->assertRedirect(route('dir-sem.semilleros.index'));
        $this->assertDatabaseHas('seedlings', ['nombre' => 'Semillero ADSO', 'codigo' => 'ADSO2026']);
    }

    public function test_codigo_con_espacios_o_simbolos_falla_validacion(): void
    {
        $director = $this->crearDirectorConCentro();

        $response = $this->actingAs($director)->post(route('dir-sem.semilleros.store'), [
            'nombre' => 'Semillero Invalido',
            'codigo' => 'ADSO-2026 #1',
        ]);

        $response->assertSessionHasErrors('codigo');
        $this->assertDatabaseMissing('seedlings', ['nombre' => 'Semillero Invalido']);
    }

    public function test_codigo_vacio_sigue_autocompletando_con_sugerencia_numerica(): void
    {
        $director = $this->crearDirectorConCentro();
        Seedling::create([
            'creator_id' => $director->id, 'training_center_id' => $director->training_center_id,
            'nombre' => 'Existente', 'codigo' => '500', 'logo' => '', 'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($director)->post(route('dir-sem.semilleros.store'), [
            'nombre' => 'Semillero Auto',
            'codigo' => '',
        ]);

        $response->assertRedirect(route('dir-sem.semilleros.index'));
        $this->assertDatabaseHas('seedlings', ['nombre' => 'Semillero Auto', 'codigo' => '501']);
    }

    public function test_codigo_vacio_sin_historial_numerico_no_rompe(): void
    {
        // Ningún semillero previo, o solo códigos alfanuméricos — la
        // sugerencia debe seguir generando un valor válido sin excepción.
        $director = $this->crearDirectorConCentro();
        Seedling::create([
            'creator_id' => $director->id, 'training_center_id' => $director->training_center_id,
            'nombre' => 'Existente Alfa', 'codigo' => 'ADSO2025', 'logo' => '', 'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($director)->post(route('dir-sem.semilleros.store'), [
            'nombre' => 'Semillero Auto Dos',
            'codigo' => '',
        ]);

        $response->assertRedirect(route('dir-sem.semilleros.index'));
        $this->assertDatabaseHas('seedlings', ['nombre' => 'Semillero Auto Dos']);
    }
}
