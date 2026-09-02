<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-012
 * Al crear un semillero con un código numérico fuera del rango de la
 * columna `codigo` (INT de MySQL, máximo 2147483647), la validación de
 * Laravel lo aceptaba (solo exigía integer|min:1, sin tope superior) y el
 * INSERT fallaba con QueryException 1264 "Out of range value for column
 * codigo" — un error 500 sin control en vez de un mensaje de validación.
 * Corregido originalmente: agregado max:2147483647 a la regla de validación.
 *
 * BUG-20260813-045: codigo pasó de integer a varchar(50) alfanumérico —
 * ya no existe un "rango" numérico que desbordar, así que el límite ahora
 * es la longitud de la columna (50 caracteres). Test actualizado para
 * reflejar esa nueva restricción sin perder el espíritu original: la
 * validación debe atrapar el caso inválido antes de llegar a un
 * QueryException sin control.
 */
class BUG20260813012Test extends TestCase
{
    use RefreshDatabase;

    private function crearDirectorConCentro(): User
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 913,
        ]);

        Permission::firstOrCreate(['name' => 'semilleros.crear', 'guard_name' => 'web']);
        $rol = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rol->givePermissionTo('semilleros.crear');

        $director = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $director->assignRole('director_semilleros');

        return $director;
    }

    public function test_codigo_demasiado_largo_falla_validacion_no_500(): void
    {
        $director = $this->crearDirectorConCentro();

        $response = $this->actingAs($director)->post(route('dir-sem.semilleros.store'), [
            'nombre' => 'Semillero Test',
            'codigo' => str_repeat('A', 51), // supera max:50
        ]);

        $response->assertSessionHasErrors('codigo');
        $this->assertDatabaseMissing('seedlings', ['nombre' => 'Semillero Test']);
    }

    public function test_codigo_dentro_de_rango_funciona(): void
    {
        $director = $this->crearDirectorConCentro();

        $response = $this->actingAs($director)->post(route('dir-sem.semilleros.store'), [
            'nombre' => 'Semillero Test',
            'codigo' => '2000',
        ]);

        $response->assertRedirect(route('dir-sem.semilleros.index'));
        $this->assertDatabaseHas('seedlings', ['nombre' => 'Semillero Test', 'codigo' => '2000']);
    }
}
