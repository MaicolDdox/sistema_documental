<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-042
 * El formulario de crear/editar programa de formación ya no pide "Ficha"
 * (que auto-creaba filas en el catálogo training_records) ni "Jornada".
 * Se eliminó por completo: columnas, catálogo TrainingRecord, JornadaEnum,
 * rutas y enlace del sidebar.
 */
class BUG20260813042Test extends TestCase
{
    use RefreshDatabase;

    private function crearAdminConCentro(): User
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-042']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-042', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro Test BUG-042', 'codigo' => 'BUG042', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $admin->assignRole('administrador_sistema');

        return $admin;
    }

    public function test_ruta_de_fichas_de_formacion_ya_no_existe(): void
    {
        $this->assertFalse(Route::has('admin.training-records.index'));
    }

    public function test_columna_training_record_id_y_jornada_ya_no_existen(): void
    {
        $this->assertFalse(Schema::hasColumn('training_programs', 'training_record_id'));
        $this->assertFalse(Schema::hasColumn('training_programs', 'jornada'));
    }

    public function test_tabla_training_records_ya_no_existe(): void
    {
        $this->assertFalse(Schema::hasTable('training_records'));
    }

    public function test_clases_ya_no_existen(): void
    {
        $this->assertFalse(class_exists(\App\Models\TrainingRecord::class));
        $this->assertFalse(class_exists(\App\Enums\JornadaEnum::class));
    }

    public function test_formulario_crear_programa_no_pide_ficha_ni_jornada(): void
    {
        $admin = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->get(route('admin.training-programs.create'));

        $response->assertOk();
        $response->assertDontSee('Ficha');
        $response->assertDontSee('Jornada');
    }

    public function test_se_puede_crear_programa_de_formacion_sin_ficha_ni_jornada(): void
    {
        $admin = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->post(route('admin.training-programs.store'), [
            'nombre' => 'Programa de prueba',
            'tipo' => 'Técnico',
            'modalidad' => 'presencial',
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('admin.training-programs.index'));
        $this->assertDatabaseHas('training_programs', ['nombre' => 'Programa de prueba']);
    }

    public function test_sidebar_admin_no_muestra_fichas_de_formacion(): void
    {
        $admin = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Fichas de Form.');
    }
}
