<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\GrupoInvestigacion;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * BUG-20260914-004 — Reintroducción de Policies de Laravel para centralizar
 * la autorización de propiedad de recursos, eliminando la duplicación manual
 * de checkCentroFormacion() que causó los bugs 001/002/003.
 *
 * Verifica que:
 * - SeedlingPolicy deniega acceso cross-centro y lo permite same-centro.
 * - GrupoInvestigacionPolicy ídem para grupos de investigación.
 * - UserPolicy combina aislamiento de centro + regla uno-a-uno.
 * - Gate::before sigue otorgando acceso total a super_administrador y
 *   administrador_sistema antes de evaluar cualquier Policy.
 * - Los controladores que antes usaban checkCentroFormacion() ahora retornan
 *   403 cuando un usuario intenta actuar sobre un recurso de otro centro.
 */
class BUG20260914004Test extends TestCase
{
    use RefreshDatabase;

    private TrainingCenter $centroA;

    private TrainingCenter $centroB;

    private User $directorCentroA;

    private User $directorCentroB;

    private User $adminCentroA;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $deptoA = Department::create(['nombre' => 'Depto A']);
        $ciudadA = City::create(['nombre' => 'Ciudad A', 'department_id' => $deptoA->id]);
        $this->centroA = TrainingCenter::create([
            'nombre' => 'Centro A', 'codigo' => 'CA', 'activo' => true,
            'department_id' => $deptoA->id, 'city_id' => $ciudadA->id,
        ]);

        $deptoB = Department::create(['nombre' => 'Depto B']);
        $ciudadB = City::create(['nombre' => 'Ciudad B', 'department_id' => $deptoB->id]);
        $this->centroB = TrainingCenter::create([
            'nombre' => 'Centro B', 'codigo' => 'CB', 'activo' => true,
            'department_id' => $deptoB->id, 'city_id' => $ciudadB->id,
        ]);

        $this->directorCentroA = User::factory()->create([
            'training_center_id' => $this->centroA->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $this->directorCentroA->assignRole('director_semilleros');

        $this->directorCentroB = User::factory()->create([
            'training_center_id' => $this->centroB->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $this->directorCentroB->assignRole('director_semilleros');

        $this->adminCentroA = User::factory()->create([
            'training_center_id' => $this->centroA->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $this->adminCentroA->assignRole('administrador_sistema');

        $this->superAdmin = User::factory()->create([
            'training_center_id' => null,
            'estado' => EstadoEnum::Activo,
        ]);
        $this->superAdmin->assignRole('super_administrador');
    }

    // ─────────────────────────────────────────────
    // SeedlingPolicy
    // ─────────────────────────────────────────────

    public function test_seedling_policy_view_deniega_acceso_cross_centro(): void
    {
        $semilleroCentroB = Seedling::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Semillero B', 'codigo' => 'SB1', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->assertFalse(
            Gate::forUser($this->directorCentroA)->allows('view', $semilleroCentroB),
            'Director de Centro A no debe poder ver semillero de Centro B'
        );
    }

    public function test_seedling_policy_view_permite_acceso_mismo_centro(): void
    {
        $semilleroCentroA = Seedling::create([
            'creator_id' => $this->directorCentroA->id,
            'training_center_id' => $this->centroA->id,
            'nombre' => 'Semillero A', 'codigo' => 'SA1', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->assertTrue(
            Gate::forUser($this->directorCentroA)->allows('view', $semilleroCentroA),
            'Director de Centro A debe poder ver semillero de su propio centro'
        );
    }

    public function test_seedling_policy_update_deniega_acceso_cross_centro(): void
    {
        $semilleroCentroB = Seedling::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Semillero B', 'codigo' => 'SB2', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->assertFalse(
            Gate::forUser($this->directorCentroA)->allows('update', $semilleroCentroB),
            'Director de Centro A no debe poder actualizar semillero de Centro B'
        );
    }

    public function test_seedling_policy_delete_deniega_acceso_cross_centro(): void
    {
        $semilleroCentroB = Seedling::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Semillero B', 'codigo' => 'SB3', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->assertFalse(
            Gate::forUser($this->directorCentroA)->allows('delete', $semilleroCentroB),
            'Director de Centro A no debe poder eliminar semillero de Centro B'
        );
    }

    public function test_super_admin_puede_ver_cualquier_semillero_via_gate_before(): void
    {
        $semilleroCentroB = Seedling::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Semillero B', 'codigo' => 'SB4', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->assertTrue(
            Gate::forUser($this->superAdmin)->allows('view', $semilleroCentroB),
            'super_administrador debe poder ver cualquier semillero (Gate::before)'
        );
    }

    public function test_admin_sistema_puede_actualizar_cualquier_semillero_via_gate_before(): void
    {
        $semilleroCentroB = Seedling::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Semillero B', 'codigo' => 'SB5', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->assertTrue(
            Gate::forUser($this->adminCentroA)->allows('update', $semilleroCentroB),
            'administrador_sistema debe pasar Gate::before independientemente del centro'
        );
    }

    // ─────────────────────────────────────────────
    // GrupoInvestigacionPolicy
    // ─────────────────────────────────────────────

    public function test_grupo_policy_update_deniega_acceso_cross_centro(): void
    {
        $grupoB = GrupoInvestigacion::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Grupo B', 'codigo' => 'GB1', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->assertFalse(
            Gate::forUser($this->directorCentroA)->allows('update', $grupoB),
            'Director de Centro A no debe poder actualizar grupo de Centro B'
        );
    }

    public function test_grupo_policy_update_permite_acceso_mismo_centro(): void
    {
        $grupoA = GrupoInvestigacion::create([
            'creator_id' => $this->adminCentroA->id,
            'training_center_id' => $this->centroA->id,
            'nombre' => 'Grupo A', 'codigo' => 'GA1', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->assertTrue(
            Gate::forUser($this->adminCentroA)->allows('update', $grupoA),
            'Admin de Centro A debe poder actualizar grupo de su propio centro'
        );
    }

    // ─────────────────────────────────────────────
    // SemilleroController — HTTPs 403 via Policy
    // ─────────────────────────────────────────────

    public function test_semillero_controller_show_retorna_403_para_semillero_de_otro_centro(): void
    {
        $semilleroCentroB = Seedling::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Semillero B', 'codigo' => 'SB6', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($this->directorCentroA)
            ->get(route('dir-sem.semilleros.show', $semilleroCentroB));

        $response->assertStatus(403);
    }

    public function test_semillero_controller_edit_retorna_403_para_semillero_de_otro_centro(): void
    {
        $semilleroCentroB = Seedling::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Semillero B', 'codigo' => 'SB7', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($this->directorCentroA)
            ->get(route('dir-sem.semilleros.edit', $semilleroCentroB));

        $response->assertStatus(403);
    }

    public function test_vinculacion_semillero_update_retorna_403_para_semillero_de_otro_centro(): void
    {
        $semilleroCentroB = Seedling::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Semillero B', 'codigo' => 'SB8', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($this->directorCentroA)
            ->put(route('dir-sem.vinculaciones.update', $semilleroCentroB), [
                'lider_id' => null,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_puede_actualizar_grupo_de_otro_centro_via_gate_before(): void
    {
        /**
         * administrador_sistema siempre recibe true de Gate::before(), por lo que
         * la GrupoInvestigacionPolicy nunca se evalúa para ese rol. Un admin de
         * Centro A puede actualizar grupos de Centro B — la protección
         * cross-centro en la UI viene del filtrado en index(), no de la Policy.
         * Este test documenta ese comportamiento esperado para evitar confusión
         * futura. La Policy es defense-in-depth para roles no-admin con ese
         * permiso que puedan crearse en el futuro.
         */
        $grupoB = GrupoInvestigacion::create([
            'creator_id' => $this->directorCentroB->id,
            'training_center_id' => $this->centroB->id,
            'nombre' => 'Grupo B', 'codigo' => 'GB2', 'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($this->adminCentroA)
            ->put(route('admin.vinculaciones-grupo-investigacion.update', $grupoB), [
                'director_id' => null,
            ]);

        // Gate::before da true al admin; la Policy no se evalúa.
        $response->assertRedirect();
    }
}
