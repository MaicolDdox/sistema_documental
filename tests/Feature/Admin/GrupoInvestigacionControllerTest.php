<?php

namespace Tests\Feature\Admin;

use App\Models\City;
use App\Models\Department;
use App\Models\GrupoInvestigacion;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reforma GDI/SDI: administrador_sistema crea grupo_investigacion con datos
 * básicos (nombre, código, centro heredado del admin) — descripción, logo y
 * línea de investigación principal quedan null hasta que
 * director_grupo_investigacion los completa (ver
 * DirectorGrupoInvestigacion\GrupoInvestigacionControllerTest).
 */
class GrupoInvestigacionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdminConCentro(): array
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto GI']);
        $ciudad = City::create(['nombre' => 'Ciudad GI', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro GI', 'codigo' => 'GI-001', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');

        return [$admin, $centro];
    }

    public function test_admin_crea_grupo_con_datos_basicos_y_hereda_su_centro(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();

        $response = $this->actingAs($admin)->post(route('admin.grupos-investigacion.store'), [
            'nombre' => 'Grupo de Biotecnología',
            'codigo' => 'GB001',
        ]);

        $response->assertRedirect(route('admin.grupos-investigacion.index'));
        $grupo = GrupoInvestigacion::where('codigo', 'GB001')->firstOrFail();
        $this->assertSame('Grupo de Biotecnología', $grupo->nombre);
        $this->assertSame($centro->id, $grupo->training_center_id);
        $this->assertSame($admin->id, $grupo->creator_id);
    }

    public function test_grupo_recien_creado_no_tiene_descripcion_logo_ni_linea_ni_director(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $this->actingAs($admin)->post(route('admin.grupos-investigacion.store'), [
            'nombre' => 'Grupo Sin Completar',
            'codigo' => 'GSC001',
        ]);

        $grupo = GrupoInvestigacion::where('codigo', 'GSC001')->firstOrFail();
        $this->assertNull($grupo->descripcion);
        $this->assertNull($grupo->logo);
        $this->assertCount(0, $grupo->lineasInvestigacion);
        $this->assertNull($grupo->director_id);
    }

    public function test_admin_no_puede_editar_descripcion_logo_ni_linea_desde_su_formulario(): void
    {
        [$admin] = $this->crearAdminConCentro();

        $grupo = GrupoInvestigacion::create([
            'training_center_id' => $admin->training_center_id,
            'creator_id' => $admin->id,
            'nombre' => 'Grupo Editable',
            'codigo' => 'GE001',
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.grupos-investigacion.update', $grupo), [
            'nombre' => 'Grupo Editable Renombrado',
            'codigo' => 'GE001',
            // Aunque se envíen, el controller del admin no los acepta en la
            // validación — solo nombre/código son campos "básicos" suyos.
            'descripcion' => 'Intento de override',
            'logo' => 'logo-hackeado.png',
        ]);

        $response->assertRedirect(route('admin.grupos-investigacion.index'));
        $grupo->refresh();
        $this->assertSame('Grupo Editable Renombrado', $grupo->nombre);
        $this->assertNull($grupo->descripcion);
        $this->assertNull($grupo->logo);
    }

    public function test_admin_no_ve_grupos_de_otro_centro(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();
        $otroCentro = TrainingCenter::create([
            'nombre' => 'Otro Centro GI', 'codigo' => 'GI-002', 'activo' => true,
            'department_id' => $centro->department_id, 'city_id' => $centro->city_id,
        ]);

        $grupoAjeno = GrupoInvestigacion::create([
            'training_center_id' => $otroCentro->id,
            'creator_id' => $admin->id,
            'nombre' => 'Grupo Ajeno',
            'codigo' => 'GA001',
            'estado' => 'activo',
        ]);

        $this->actingAs($admin)->get(route('admin.grupos-investigacion.show', $grupoAjeno))
            ->assertForbidden();
    }
}
