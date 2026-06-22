<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\ResearchGroup;
use App\Models\ResearchGroupUser;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260609-005
 * InvestigadorController::store() validaba tipo_documento con
 * Rule::in(['cedula ciudadana', 'tarjeta identidad', 'cedula extranjeria', 'pasaporte'])
 * (lista hardcodeada con valores incorrectos). Al agregar un nuevo tipo al enum
 * o cambiar un valor, la validación quedaba desincronizada silenciosamente.
 * Corregido: 2026-06-09 — Rule::enum(TipoDocumentoEnum::class)
 */
class BUG20260609005Test extends TestCase
{
    use RefreshDatabase;

    private function crearTrainingCenter(): TrainingCenter
    {
        $dpto   = Department::firstOrCreate(['nombre' => 'Depto Dir']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Dir', 'department_id' => $dpto->id]);

        return TrainingCenter::create([
            'department_id' => $dpto->id,
            'city_id'       => $ciudad->id,
            'nombre'        => 'Centro Dir',
            'codigo'        => 999,
        ]);
    }

    private function crearDirector(): User
    {
        Role::firstOrCreate(['name' => 'director_investigacion', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'investigador_asociado', 'guard_name' => 'web']);

        $centro = $this->crearTrainingCenter();

        $director = User::factory()->create([
            'training_center_id' => $centro->id,
            'estado'             => EstadoEnum::Activo,
        ]);
        $director->assignRole('director_investigacion');
        $director->person()->create([
            'primer_nombre'       => 'Pedro',
            'primer_apellido'     => 'Director',
            'genero'              => 'masculino',
            'celular'             => 310000000,
            'eps'                 => 'Sura',
            'email_institucional' => $director->email,
        ]);

        $grupo = ResearchGroup::create([
            'training_center_id' => $centro->id,
            'nombre'             => 'Grupo Director',
            'director_id'        => $director->id,
            'estado'             => EstadoEnum::Activo,
        ]);

        ResearchGroupUser::create([
            'research_group_id' => $grupo->id,
            'user_id'           => $director->id,
            'rol'               => 'director',
        ]);

        return $director;
    }

    public function test_tipo_documento_invalido_falla_validacion(): void
    {
        $director = $this->crearDirector();

        $response = $this->actingAs($director)->post(route('director.investigadores.store'), [
            'email'                 => 'inv@test.com',
            'tipo_documento'        => 'tipo_inventado_que_no_existe',
            'numero_documento'      => '11223344',
            'primer_nombre'         => 'Carlos',
            'primer_apellido'       => 'Gómez',
            'cvlac_link'            => 'http://cvlac.example.com/carlos',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('tipo_documento');
        $this->assertDatabaseMissing('users', ['numero_documento' => '11223344']);
    }

    public function test_tipo_documento_valido_del_enum_pasa_validacion(): void
    {
        $director = $this->crearDirector();

        $response = $this->actingAs($director)->post(route('director.investigadores.store'), [
            'email'                 => 'inv2@test.com',
            'tipo_documento'        => TipoDocumentoEnum::Pasaporte->value,
            'numero_documento'      => '55443322',
            'primer_nombre'         => 'Laura',
            'primer_apellido'       => 'Herrera',
            'cvlac_link'            => 'http://cvlac.example.com/laura',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'numero_documento' => '55443322',
            'tipo_documento'   => TipoDocumentoEnum::Pasaporte->value,
        ]);
    }

    public function test_lista_hardcodeada_anterior_tenia_valores_incorrectos(): void
    {
        $valoresEnum  = collect(TipoDocumentoEnum::cases())->pluck('value')->sort()->values()->toArray();
        $listaAntigua = collect(['cedula ciudadana', 'tarjeta identidad', 'cedula extranjeria', 'pasaporte'])->sort()->values()->toArray();

        // La lista antigua NO coincide con el enum — por eso se reemplazó con Rule::enum()
        $this->assertNotEquals(
            $listaAntigua,
            $valoresEnum,
            'La lista hardcodeada antigua era incorrecta vs los valores reales del TipoDocumentoEnum'
        );

        // Los valores correctos del enum son:
        $this->assertContains('cedula ciudadana', $valoresEnum);
        $this->assertContains('pasaporte', $valoresEnum);
        $this->assertContains('cedula extrangera', $valoresEnum);
        $this->assertContains('documento identidad', $valoresEnum);
    }
}
