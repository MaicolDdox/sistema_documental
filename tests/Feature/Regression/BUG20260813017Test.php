<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Project;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-017
 * Auditoría completa del rol Líder de Proyecto. Hallazgo principal:
 * projects.lider_proyecto_user_id no tenía restricción de unicidad —
 * un Líder de Semillero podía asignar al mismo Líder de Proyecto a dos
 * proyectos distintos. Como LiderProyectoContext::miProyecto() asume
 * exactamente un proyecto por líder (Project::where(...)->firstOrFail()),
 * el líder quedaba "atrapado" viendo solo uno de sus proyectos, sin error
 * ni forma de acceder al resto.
 * Corregido: ProyectosController::validarProyecto() ahora valida que el
 * líder de proyecto elegido no esté ya asignado a otro proyecto (unique
 * ignorando el proyecto actual en edición).
 * También se corrigió HTML inválido en lider_proyecto/aprendices/index.blade.php
 * (un <form> quedaba abierto dentro de una fila de tabla, cerrándose a
 * mitad de una celda) usando el atributo form="" en vez de anidar el form.
 */
class BUG20260813017Test extends TestCase
{
    use RefreshDatabase;

    private function crearEscenario(): array
    {
        $dpto = Department::firstOrCreate(['nombre' => 'Depto Test']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test', 'department_id' => $dpto->id]);
        $centro = TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id' => $ciudad->id,
            'nombre' => 'Centro Test',
            'codigo' => 919,
        ]);

        foreach (['proyectos.crear', 'proyectos.editar', 'usuarios.crear_lider_proyecto'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $rol = Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);
        $rol->givePermissionTo(['proyectos.crear', 'proyectos.editar', 'usuarios.crear_lider_proyecto']);
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);

        $liderSemillero = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $liderSemillero->assignRole('lider_semillero');

        $semillero = Seedling::create([
            'creator_id' => $liderSemillero->id,
            'leader_id' => $liderSemillero->id,
            'training_center_id' => $centro->id,
            'nombre' => 'Semillero Test',
            'codigo' => 2401,
            'logo' => '',
            'estado' => EstadoEnum::Activo,
        ]);

        $liderProyecto = User::factory()->create([
            'training_center_id' => $centro->id,
            'created_by_user_id' => $liderSemillero->id,
        ]);
        $liderProyecto->assignRole('lider_proyecto');

        $researchLine = ResearchLine::create(['nombre' => 'Línea Test', 'estado' => EstadoEnum::Activo]);

        return [$liderSemillero, $semillero, $liderProyecto, $researchLine];
    }

    public function test_no_se_puede_asignar_el_mismo_lider_de_proyecto_a_dos_proyectos(): void
    {
        [$liderSemillero, $semillero, $liderProyecto, $researchLine] = $this->crearEscenario();

        Project::create([
            'project_creator_id' => $liderSemillero->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Primer Proyecto',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        $response = $this->actingAs($liderSemillero)->post(route('lider-sem.proyectos.store'), [
            'nombre' => 'Segundo Proyecto',
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
        ]);

        $response->assertSessionHasErrors('lider_proyecto_user_id');
        $this->assertDatabaseMissing('projects', ['nombre' => 'Segundo Proyecto']);
    }

    public function test_editar_proyecto_sin_cambiar_lider_no_dispara_error_de_unicidad(): void
    {
        [$liderSemillero, $semillero, $liderProyecto, $researchLine] = $this->crearEscenario();

        $proyecto = Project::create([
            'project_creator_id' => $liderSemillero->id,
            'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
            'nombre' => 'Proyecto Original',
            'estado' => EstadoEnum::Activo,
            'fecha_inicio' => now(),
        ]);

        $response = $this->actingAs($liderSemillero)->put(route('lider-sem.proyectos.update', $proyecto), [
            'nombre' => 'Proyecto Renombrado',
            'lider_proyecto_user_id' => $liderProyecto->id,
            'research_line_id' => $researchLine->id,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('projects', ['id' => $proyecto->id, 'nombre' => 'Proyecto Renombrado']);
    }

    public function test_aprendices_index_no_anida_form_dentro_de_celda_de_tabla(): void
    {
        $contenido = file_get_contents(resource_path('views/lider_proyecto/aprendices/index.blade.php'));

        // El form de "Guardar" ya no debe abrirse antes de cerrar sus <td>: se
        // usa el atributo form="" para asociar inputs fuera del <form>.
        $this->assertStringContainsString('form="form-aprendiz-', $contenido);
    }
}
