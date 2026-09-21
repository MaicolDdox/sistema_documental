<?php

namespace Tests\Feature\Admin;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\GrupoInvestigacion;
use App\Models\MincienciasProduct;
use App\Models\ResearchLine;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reforma GDI/SDI: administrador_sistema pierde la aprobación/rechazo de
 * productos Minciencias (esa función pasa a director_grupo_investigacion,
 * acotada a su propio grupo — ver DirectorGrupoInvestigacion\MincienciasProductoControllerTest),
 * pero CONSERVA una vista de solo lectura (index/show) de los productos de
 * su centro.
 *
 * NOTA: al momento de escribir este test, resources/views/admin/minciencias/show.blade.php
 * todavía referencia route('admin.minciencias.aprobar'/'rechazar') (rutas ya
 * eliminadas), lo que rompe el render de esa vista con un
 * RouteNotFoundException. Es un hallazgo de la Fase 5 reportado aparte, no
 * corregido en este test — test_admin_puede_ver_detalle_de_solo_lectura
 * quedará en rojo hasta que se corrija esa vista.
 */
class MincienciasProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(): array
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto AdminMinc']);
        $ciudad = City::create(['nombre' => 'Ciudad AdminMinc', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro AdminMinc', 'codigo' => 'AM-001', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');

        $director = User::factory()->create(['training_center_id' => $centro->id]);
        $director->assignRole('director_grupo_investigacion');

        $grupo = GrupoInvestigacion::create([
            'training_center_id' => $centro->id,
            'creator_id' => $admin->id,
            'director_id' => $director->id,
            'nombre' => 'Grupo AdminMinc',
            'codigo' => 'G-AM-001',
            'estado' => EstadoEnum::Activo,
        ]);

        $coInv = User::factory()->create(['training_center_id' => $centro->id, 'grupo_investigacion_id' => $grupo->id]);
        $coInv->assignRole('co_investigador_gdi');

        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id,
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => $grupo->id,
            'research_line_id' => ResearchLine::firstOrCreate(['training_center_id' => $centro->id, 'nombre' => 'Linea AdminMinc'])->id,
            'nombre' => 'Producto solo lectura',
            'estado' => EstadoEnum::Activo,
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        return [$admin, $producto];
    }

    public function test_rutas_de_aprobar_y_rechazar_ya_no_existen_para_admin(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.minciencias.aprobar'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.minciencias.rechazar'));
    }

    public function test_admin_puede_ver_el_indice_de_solo_lectura(): void
    {
        [$admin, $producto] = $this->crearEscenario();

        $response = $this->actingAs($admin)->get(route('admin.minciencias.index'));

        $response->assertOk();
        $response->assertSee($producto->nombre);
    }

    public function test_admin_puede_ver_detalle_de_solo_lectura(): void
    {
        [$admin, $producto] = $this->crearEscenario();

        $this->actingAs($admin)->get(route('admin.minciencias.show', $producto))->assertOk();
    }

    /**
     * NOTA: administrador_sistema (y super_administrador) tienen un
     * Gate::before global en AppServiceProvider que los hace pasar
     * CUALQUIER $this->authorize()/can() sin mirar sus permisos Spatie
     * reales (ver app/Providers/AppServiceProvider.php). Por eso quitar
     * 'minciencias.aprobar'/'rechazar' de su lista de permisos en el seeder
     * no es, por sí solo, lo que le impide aprobar — lo que realmente lo
     * bloquea es que las rutas ya no existen (ver
     * test_rutas_de_aprobar_y_rechazar_ya_no_existen_para_admin). Este test
     * documenta esa relación en vez de asumir que can() refleja el permiso.
     */
    public function test_can_de_administrador_sistema_ignora_permisos_por_el_gate_before_global(): void
    {
        [$admin] = $this->crearEscenario();

        // Gate::before ya autoriza cualquier ability para administrador_sistema,
        // aunque el permiso ya no esté en su lista del seeder.
        $this->assertTrue($admin->can('minciencias.aprobar'));
        $this->assertTrue($admin->can('minciencias.listar'));
    }
}
