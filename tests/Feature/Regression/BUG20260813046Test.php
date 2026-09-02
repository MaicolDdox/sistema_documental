<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\Seedling;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260813-046
 * "Archivos del Semillero" (lider_semillero) usaba una variable $url nunca
 * definida en el nombre del archivo como enlace — ErrorException "Undefined
 * variable $url". Solo se manifestaba con al menos 1 archivo en la lista
 * (con 0 archivos el @forelse cae en @empty y nunca ejecuta esa línea), por
 * eso pasaba desapercibido hasta subir el primer archivo.
 */
class BUG20260813046Test extends TestCase
{
    use RefreshDatabase;

    private function crearLiderConSemillero(): User
    {
        Role::firstOrCreate(['name' => 'lider_semillero', 'guard_name' => 'web']);

        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-046']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-046', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro Test BUG-046', 'codigo' => 'BUG046', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $lider = User::factory()->create(['training_center_id' => $centro->id, 'estado' => EstadoEnum::Activo]);
        $lider->assignRole('lider_semillero');

        Seedling::create([
            'creator_id' => $lider->id, 'leader_id' => $lider->id,
            'training_center_id' => $centro->id, 'nombre' => 'Semillero BUG-046',
            'codigo' => 'S046', 'logo' => '', 'estado' => EstadoEnum::Activo,
        ]);

        return $lider;
    }

    public function test_pagina_no_falla_con_al_menos_un_archivo_en_la_lista(): void
    {
        Storage::fake('public');
        $lider = $this->crearLiderConSemillero();

        // Reproduce el escenario exacto: subir un archivo primero.
        $this->actingAs($lider)->post(route('lider-sem.archivos.store'), [
            'archivo' => UploadedFile::fake()->create('documento.pdf', 100),
        ]);

        $response = $this->actingAs($lider)->get(route('lider-sem.archivos'));

        $response->assertOk();
        $response->assertSee('documento.pdf');
    }

    public function test_nombre_del_archivo_enlaza_a_la_ruta_de_descarga(): void
    {
        Storage::fake('public');
        $lider = $this->crearLiderConSemillero();

        $this->actingAs($lider)->post(route('lider-sem.archivos.store'), [
            'archivo' => UploadedFile::fake()->create('reporte.pdf', 100),
        ]);

        $archivo = \App\Models\SeedlingFile::firstOrFail();

        $response = $this->actingAs($lider)->get(route('lider-sem.archivos'));

        $response->assertOk();
        $response->assertSee(route('lider-sem.archivos.descargar', $archivo), false);
    }
}
