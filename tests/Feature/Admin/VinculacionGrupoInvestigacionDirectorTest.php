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
 * Reforma GDI/SDI: vinculación 1-a-1 grupo_investigacion <-> director_grupo_investigacion
 * (calco de VinculacionSemilleroLiderController / Seedling.leader_id).
 */
class VinculacionGrupoInvestigacionDirectorTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdminConCentro(): array
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto Vinc']);
        $ciudad = City::create(['nombre' => 'Ciudad Vinc', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro Vinc', 'codigo' => 'VINC-001', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $admin = User::factory()->create(['training_center_id' => $centro->id]);
        $admin->assignRole('administrador_sistema');

        return [$admin, $centro];
    }

    private function crearGrupo(TrainingCenter $centro, string $codigo, User $admin): GrupoInvestigacion
    {
        return GrupoInvestigacion::create([
            'training_center_id' => $centro->id,
            'creator_id' => $admin->id,
            'nombre' => "Grupo {$codigo}",
            'codigo' => $codigo,
            'estado' => 'activo',
        ]);
    }

    public function test_admin_vincula_un_director_a_un_grupo(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();
        $grupo = $this->crearGrupo($centro, 'V001', $admin);

        $director = User::factory()->create(['training_center_id' => $centro->id]);
        $director->assignRole('director_grupo_investigacion');

        $response = $this->actingAs($admin)->put(route('admin.vinculaciones-grupo-investigacion.update', $grupo), [
            'director_id' => $director->id,
        ]);

        $response->assertRedirect(route('admin.vinculaciones-grupo-investigacion.index'));
        $this->assertSame($director->id, $grupo->fresh()->director_id);
    }

    public function test_no_se_puede_vincular_el_mismo_director_a_dos_grupos(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();
        $grupoUno = $this->crearGrupo($centro, 'V002', $admin);
        $grupoDos = $this->crearGrupo($centro, 'V003', $admin);

        $director = User::factory()->create(['training_center_id' => $centro->id]);
        $director->assignRole('director_grupo_investigacion');

        $this->actingAs($admin)->put(route('admin.vinculaciones-grupo-investigacion.update', $grupoUno), [
            'director_id' => $director->id,
        ])->assertRedirect(route('admin.vinculaciones-grupo-investigacion.index'));

        $response = $this->actingAs($admin)->put(route('admin.vinculaciones-grupo-investigacion.update', $grupoDos), [
            'director_id' => $director->id,
        ]);

        $response->assertSessionHasErrors('director_id');
        $this->assertNull($grupoDos->fresh()->director_id);
        $this->assertSame($director->id, $grupoUno->fresh()->director_id);
    }

    public function test_no_se_puede_vincular_un_director_de_otro_centro(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();
        $otroCentro = TrainingCenter::create([
            'nombre' => 'Otro Centro Vinc', 'codigo' => 'VINC-002', 'activo' => true,
            'department_id' => $centro->department_id, 'city_id' => $centro->city_id,
        ]);
        $grupo = $this->crearGrupo($centro, 'V004', $admin);

        $directorAjeno = User::factory()->create(['training_center_id' => $otroCentro->id]);
        $directorAjeno->assignRole('director_grupo_investigacion');

        $response = $this->actingAs($admin)->put(route('admin.vinculaciones-grupo-investigacion.update', $grupo), [
            'director_id' => $directorAjeno->id,
        ]);

        $response->assertSessionHasErrors('director_id');
        $this->assertNull($grupo->fresh()->director_id);
    }

    public function test_no_se_puede_vincular_un_usuario_que_no_es_director_de_grupo(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();
        $grupo = $this->crearGrupo($centro, 'V005', $admin);

        $noDirector = User::factory()->create(['training_center_id' => $centro->id]);
        $noDirector->assignRole('director_semilleros');

        $response = $this->actingAs($admin)->put(route('admin.vinculaciones-grupo-investigacion.update', $grupo), [
            'director_id' => $noDirector->id,
        ]);

        $response->assertSessionHasErrors('director_id');
        $this->assertNull($grupo->fresh()->director_id);
    }

    public function test_admin_puede_desvincular_enviando_director_id_vacio(): void
    {
        [$admin, $centro] = $this->crearAdminConCentro();
        $grupo = $this->crearGrupo($centro, 'V006', $admin);

        $director = User::factory()->create(['training_center_id' => $centro->id]);
        $director->assignRole('director_grupo_investigacion');
        $grupo->update(['director_id' => $director->id]);

        $this->actingAs($admin)->put(route('admin.vinculaciones-grupo-investigacion.update', $grupo), [
            'director_id' => null,
        ])->assertRedirect(route('admin.vinculaciones-grupo-investigacion.index'));

        $this->assertNull($grupo->fresh()->director_id);
    }
}
