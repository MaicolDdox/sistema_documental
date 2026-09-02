<?php

namespace Tests\Feature\Regression;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-043
 * El formulario de actualizar perfil (settings/profile) gana el campo
 * "CVLAC" (columna people.cvlac_link, ya existía en BD pero no se exponía
 * en este formulario) y pierde el desplegable "Programa de Formación" —
 * solo en este formulario; el catálogo de programas de formación
 * (admin/training-programs) sigue intacto para su propio uso.
 */
class BUG20260813043Test extends TestCase
{
    use RefreshDatabase;

    public function test_formulario_de_perfil_muestra_cvlac_y_no_programa_de_formacion(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('CVLAC');
        $response->assertDontSee('Programa de Formación');
    }

    public function test_actualizar_perfil_guarda_el_cvlac(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'primer_nombre' => 'Ana',
            'segundo_nombre' => '',
            'primer_apellido' => 'Torres',
            'segundo_apellido' => '',
            'email' => $user->email,
            'telefono' => '',
            'celular' => '',
            'genero' => '',
            'eps' => '',
            'email_institucional' => '',
            'entity_position_id' => '',
            'linkage_type_id' => '',
            'cvlac_link' => 'https://scienti.minciencias.gov.co/cvlac/visualizador/generarCurriculoCv.do?cod_rh=0001234',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('people', [
            'user_id' => $user->id,
            'cvlac_link' => 'https://scienti.minciencias.gov.co/cvlac/visualizador/generarCurriculoCv.do?cod_rh=0001234',
        ]);
    }

    public function test_actualizar_perfil_ya_no_exige_ni_guarda_programa_de_formacion(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'primer_nombre' => 'Ana',
            'segundo_nombre' => '',
            'primer_apellido' => 'Torres',
            'segundo_apellido' => '',
            'email' => $user->email,
            'telefono' => '',
            'celular' => '',
            'genero' => '',
            'eps' => '',
            'email_institucional' => '',
            'entity_position_id' => '',
            'linkage_type_id' => '',
            'cvlac_link' => '',
            'training_program_id' => 999999, // ya no existe como campo válido, debe ignorarse
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseMissing('people', [
            'user_id' => $user->id,
            'training_program_id' => 999999,
        ]);
    }

    public function test_catalogo_de_programas_de_formacion_sigue_intacto(): void
    {
        // Solo se quitó del formulario de perfil — el catálogo en sí sigue vivo.
        $this->assertTrue(Route::has('admin.training-programs.index'));
        $this->assertTrue(class_exists(\App\Models\TrainingProgram::class));
    }
}
