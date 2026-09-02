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
 * Regresión: BUG-20260813-009
 * El modal "Editar semillero" carga el formulario dentro de un <iframe>
 * (?embedded=1). SemilleroController::update() siempre redirigía al listado
 * completo (dir-sem.semilleros.index) sin conservar el parámetro embedded,
 * por lo que tras guardar, el iframe navegaba y renderizaba la página
 * completa (con sidebar) DENTRO del modal en vez de cerrarlo.
 * Corregido: cuando la petición viene marcada como embedded (hidden input
 * propagado desde el query string), el controlador responde con una vista
 * mínima que hace window.top.location = index, rompiendo el iframe.
 * También se eliminó el campo de subir logo del formulario de editar
 * semillero (ya no se ofrece esa opción).
 */
class BUG20260813009Test extends TestCase
{
    use RefreshDatabase;

    private function crearDirectorConSemillero(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 910,
        ]);

        foreach (['semilleros.listar', 'semilleros.editar', 'semilleros.crear'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'director_semilleros', 'guard_name' => 'web']);
        $rol->givePermissionTo(['semilleros.listar', 'semilleros.editar', 'semilleros.crear']);
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
            'codigo' => 1701,
            'logo' => 'default.png',
            'estado' => EstadoEnum::Activo,
        ]);

        return [$director, $semillero];
    }

    public function test_formulario_editar_no_tiene_campo_logo(): void
    {
        [$director, $semillero] = $this->crearDirectorConSemillero();

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.edit', $semillero));

        $response->assertOk();
        $response->assertDontSee('name="logo"', false);
    }

    public function test_formulario_crear_no_tiene_campo_logo(): void
    {
        [$director] = $this->crearDirectorConSemillero();

        $response = $this->actingAs($director)->get(route('dir-sem.semilleros.create'));

        $response->assertOk();
        $response->assertDontSee('name="logo"', false);
    }

    public function test_crear_semillero_sin_logo_funciona(): void
    {
        [$director] = $this->crearDirectorConSemillero();

        $response = $this->actingAs($director)->post(route('dir-sem.semilleros.store'), [
            'nombre' => 'Semillero Nuevo Sin Logo',
        ]);

        $response->assertRedirect(route('dir-sem.semilleros.index'));
        $this->assertDatabaseHas('seedlings', ['nombre' => 'Semillero Nuevo Sin Logo']);
    }

    public function test_update_embedded_rompe_el_iframe_en_vez_de_redirigir_dentro(): void
    {
        [$director, $semillero] = $this->crearDirectorConSemillero();

        $response = $this->actingAs($director)->put(route('dir-sem.semilleros.update', $semillero), [
            'nombre' => 'Semillero Editado',
            'embedded' => '1',
        ]);

        $response->assertOk();
        $response->assertSee('window.top.location.href', false);
        $this->assertStringContainsString('director-semilleros', $response->getContent());
        $this->assertDatabaseHas('seedlings', ['id' => $semillero->id, 'nombre' => 'Semillero Editado']);
    }

    public function test_update_no_embedded_sigue_haciendo_redirect_normal(): void
    {
        [$director, $semillero] = $this->crearDirectorConSemillero();

        $response = $this->actingAs($director)->put(route('dir-sem.semilleros.update', $semillero), [
            'nombre' => 'Semillero Editado Normal',
        ]);

        $response->assertRedirect(route('dir-sem.semilleros.index'));
        $this->assertDatabaseHas('seedlings', ['id' => $semillero->id, 'nombre' => 'Semillero Editado Normal']);
    }
}
