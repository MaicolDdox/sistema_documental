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
 * Regresión: BUG-20260813-013
 * En "Gestión de Semilleros", el botón del menú de tres puntos decía
 * "Eliminar" pero en realidad apuntaba a SemilleroController::destroy(),
 * que hace un borrado real (`$semillero->delete()`) sin las validaciones de
 * negocio que sí tiene toggleEstado() (p. ej. no desactivar si hay proyectos
 * activos). El botón ahora llama a la ruta toggle-estado y el texto/modal
 * son dinámicos: "Desactivar" cuando el semillero está activo, "Activar"
 * cuando está inactivo.
 * Corregido: 2026-08-13.
 */
class BUG20260813013Test extends TestCase
{
    use RefreshDatabase;

    private function crearDirectorConSemillero(EstadoEnum $estado = EstadoEnum::Activo): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 914,
        ]);

        foreach (['semilleros.listar', 'semilleros.editar', 'semilleros.activar_desactivar'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rol->givePermissionTo(['semilleros.listar', 'semilleros.editar', 'semilleros.activar_desactivar']);
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);

        $director = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $director->assignRole('director_semilleros');

        $semillero = Seedling::create([
            'creator_id' => $director->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 2001,
            'logo' => '',
            'estado' => $estado,
        ]);

        return [$director, $semillero];
    }

    public function test_listado_no_ofrece_boton_eliminar_real(): void
    {
        [$director] = $this->crearDirectorConSemillero();

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.index'));

        $response->assertOk();
        $response->assertDontSee('>Eliminar<', false);
    }

    public function test_semillero_activo_muestra_boton_desactivar_apuntando_a_toggle_estado(): void
    {
        [$director, $semillero] = $this->crearDirectorConSemillero(EstadoEnum::Activo);

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.index'));

        $response->assertOk();
        $response->assertSee('>Desactivar<', false);
        $response->assertSee(route('dir-sem.semilleros.toggle-estado', $semillero));
    }

    public function test_semillero_inactivo_muestra_boton_activar(): void
    {
        [$director, $semillero] = $this->crearDirectorConSemillero(EstadoEnum::Inactivo);

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.index'));

        $response->assertOk();
        $response->assertSee('>Activar<', false);
    }

    public function test_click_desactivar_no_borra_el_registro(): void
    {
        [$director, $semillero] = $this->crearDirectorConSemillero(EstadoEnum::Activo);

        $response = $this->actingAs($director)->post(route('dir-sem.semilleros.toggle-estado', $semillero));

        $response->assertRedirect();
        $this->assertDatabaseHas('seedlings', ['id' => $semillero->id, 'estado' => 'inactivo']);
    }
}
