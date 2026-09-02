<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
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
 * Regresión: BUG-20260813-008
 * Reporte de bugs del panel de Director de Semilleros:
 * 1. Dashboard: eliminada la card "Acciones Rápidas".
 * 2. SemilleroController::edit() llamaba a Builder::orWhereKey(), método
 *    inexistente en el Query/Eloquent Builder de Laravel — BadMethodCallException
 *    al abrir "Editar Semillero". Corregido a ->orWhere('id', ...).
 * 3. El modal "Nuevo Líder" (director_semilleros/lideres/index.blade.php) no
 *    tenía el campo obligatorio "tipo_documento" — la validación fallaba en
 *    silencio (redirect 302 de vuelta a la misma página, sin campo donde
 *    mostrar el error) y nunca se creaba el líder.
 * Corregido: 2026-08-13.
 */
class BUG20260813008Test extends TestCase
{
    use RefreshDatabase;

    private function crearDirectorConCentro(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 909,
        ]);

        foreach ([
            'semilleros.listar', 'semilleros.editar', 'usuarios.crear_lider_semillero', 'usuarios.listar',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rol->givePermissionTo(['semilleros.listar', 'semilleros.editar', 'usuarios.crear_lider_semillero', 'usuarios.listar']);
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);

        $director = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $director->assignRole('director_semilleros');

        return [$director, $centro];
    }

    public function test_dashboard_no_muestra_acciones_rapidas(): void
    {
        [$director] = $this->crearDirectorConCentro();

        $response = $this->actingAs($director)->get(route('dir-sem.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Acciones Rápidas');
    }

    public function test_editar_semillero_no_truena_por_orwherekey(): void
    {
        [$director, $centro] = $this->crearDirectorConCentro();

        $lider = User::factory()->create(['training_center_id' => $centro->id]);
        $lider->assignRole('lider_semillero');

        $semillero = Seedling::create([
            'creator_id' => $director->id,
            'leader_id' => $lider->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 1601,
            'logo' => 'default.png',
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.edit', $semillero));

        $response->assertOk();
    }

    public function test_modal_nuevo_lider_tiene_campo_tipo_documento(): void
    {
        [$director] = $this->crearDirectorConCentro();

        $response = $this->actingAs($director)->get(route('dir-sem.lideres.index'));

        $response->assertOk();
        $response->assertSee('name="tipo_documento"', false);
    }

    public function test_crear_lider_desde_modal_con_tipo_documento_si_registra_el_lider(): void
    {
        [$director] = $this->crearDirectorConCentro();

        $response = $this->actingAs($director)->post(route('dir-sem.lideres.store'), [
            '_from_modal' => '1',
            'nombre' => 'Nuevo',
            'apellido' => 'Lider',
            'tipo_documento' => TipoDocumentoEnum::CedulaCiudadana->value,
            'numero_documento' => '777888999',
            'email' => 'nuevolider@test.com',
        ]);

        $response->assertRedirect(route('dir-sem.lideres.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevolider@test.com']);
        $nuevoLider = User::where('email', 'nuevolider@test.com')->first();
        $this->assertTrue($nuevoLider->hasRole('lider_semillero'));
    }

    public function test_crear_lider_sin_tipo_documento_falla_validacion_no_crea_usuario(): void
    {
        [$director] = $this->crearDirectorConCentro();

        $response = $this->actingAs($director)->post(route('dir-sem.lideres.store'), [
            '_from_modal' => '1',
            'nombre' => 'Sin',
            'apellido' => 'Documento',
            'numero_documento' => '111222333',
            'email' => 'sindoc@test.com',
        ]);

        $response->assertSessionHasErrors('tipo_documento');
        $this->assertDatabaseMissing('users', ['email' => 'sindoc@test.com']);
    }
}
