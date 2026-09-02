<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\InvestigationType;
use App\Models\Project;
use App\Models\ProjectModality;
use App\Models\ResearchLine;
use App\Models\Seedling;
use App\Models\TechnologicalLine;
use App\Models\ThematicArea;
use App\Models\TrainingCenter;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-047
 * Formulario "Registrar aprendiz" (lider_proyecto): agrega Teléfono y
 * Correo electrónico; reemplaza el campo de texto libre "Nombre de
 * tecnólogo" por "Programa de Formación", conectado al catálogo real
 * training_programs que administra administrador_sistema.
 */
class BUG20260813047Test extends TestCase
{
    use RefreshDatabase;

    private function crearLiderProyectoConProyecto(): array
    {
        Role::firstOrCreate(['name' => 'lider_proyecto', 'guard_name' => 'web']);

        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-047']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-047', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro Test BUG-047', 'codigo' => 'BUG047', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $lider = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $lider->assignRole('lider_proyecto');

        $semillero = Seedling::create([
            'creator_id' => $lider->id, 'training_center_id' => $centro->id,
            'nombre' => 'Semillero BUG-047', 'codigo' => 'S047', 'logo' => '', 'estado' => EstadoEnum::Activo,
        ]);

        $proyecto = Project::create([
            'project_creator_id' => $lider->id, 'seedling_id' => $semillero->id,
            'lider_proyecto_user_id' => $lider->id,
            'research_line_id' => ResearchLine::firstOrCreate(['nombre' => 'Linea BUG-047'])->id,
            'technological_line_id' => TechnologicalLine::firstOrCreate(['nombre' => 'Linea Tec BUG-047'])->id,
            'thematic_area_id' => ThematicArea::firstOrCreate(['nombre' => 'Area BUG-047'])->id,
            'project_modality_id' => ProjectModality::firstOrCreate(['nombre' => 'Modalidad BUG-047'])->id,
            'investigation_type_id' => InvestigationType::firstOrCreate(['nombre' => 'Tipo BUG-047'])->id,
            'nombre' => 'Proyecto BUG-047', 'fecha_inicio' => now(), 'estado' => 'activo',
        ]);

        $tipo = TrainingProgramType::firstOrCreate(['nombre' => 'Tecnólogo BUG-047']);
        $programa = TrainingProgram::create([
            'training_program_type_id' => $tipo->id,
            'nombre' => 'Análisis y Desarrollo de Software',
            'modalidad' => 'presencial', 'estado' => 'activo',
        ]);

        return [$lider, $proyecto, $programa];
    }

    public function test_formulario_muestra_telefono_correo_y_programa_de_formacion(): void
    {
        [$lider] = $this->crearLiderProyectoConProyecto();

        $response = $this->actingAs($lider)->get(route('lider-proyecto.aprendices.index'));

        $response->assertOk();
        $response->assertSee('Teléfono');
        $response->assertSee('Correo electrónico');
        $response->assertSee('Programa de Formación');
        $response->assertDontSee('Nombre de tecnólogo');
    }

    public function test_registrar_aprendiz_con_telefono_correo_y_programa_conectado_al_catalogo(): void
    {
        [$lider, , $programa] = $this->crearLiderProyectoConProyecto();

        $response = $this->actingAs($lider)->post(route('lider-proyecto.aprendices.store'), [
            'nombre_completo' => 'Aprendiz BUG-047',
            'numero_documento' => '999888777',
            'ficha' => '2600047',
            'telefono' => '3001234567',
            'email' => 'aprendiz@test.com',
            'training_program_id' => $programa->id,
        ]);

        $response->assertRedirect(route('lider-proyecto.aprendices.index'));
        $this->assertDatabaseHas('project_learners', [
            'nombre_completo' => 'Aprendiz BUG-047',
            'telefono' => '3001234567',
            'email' => 'aprendiz@test.com',
            'training_program_id' => $programa->id,
        ]);
    }

    public function test_programa_de_formacion_es_obligatorio(): void
    {
        [$lider] = $this->crearLiderProyectoConProyecto();

        $response = $this->actingAs($lider)->post(route('lider-proyecto.aprendices.store'), [
            'nombre_completo' => 'Sin Programa',
            'numero_documento' => '111222333',
            'ficha' => '2600048',
        ]);

        $response->assertSessionHasErrors('training_program_id');
        $this->assertDatabaseMissing('project_learners', ['nombre_completo' => 'Sin Programa']);
    }

    public function test_nuevo_programa_creado_por_admin_aparece_disponible_en_el_formulario(): void
    {
        [$lider] = $this->crearLiderProyectoConProyecto();

        $nuevoTipo = TrainingProgramType::firstOrCreate(['nombre' => 'Técnico BUG-047']);
        TrainingProgram::create([
            'training_program_type_id' => $nuevoTipo->id,
            'nombre' => 'Programa Nuevo Del Admin',
            'modalidad' => 'virtual', 'estado' => 'activo',
        ]);

        $response = $this->actingAs($lider)->get(route('lider-proyecto.aprendices.index'));

        $response->assertOk();
        $response->assertSee('Programa Nuevo Del Admin');
    }
}
