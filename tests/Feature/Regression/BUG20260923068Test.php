<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\GrupoInvestigacion;
use App\Models\MincienciasProduct;
use App\Models\ResearchLine;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * BUG-20260923-068 — Los archivos de productos Minciencias se mostraban (y
 * descargaban) con el nombre aleatorio que Laravel usa para guardarlos en
 * disco (p. ej. "5AO6589RKir1QYSDeNcCnA6UDpFmjW0UpCLLYr2s.txt") en vez del
 * nombre original con el que el usuario los subió, porque
 * minciencias_product_files nunca guardaba ese nombre.
 *
 * Se agrega la columna nombre_original y se usa como respaldo antes del
 * basename() del path guardado, tanto en pantalla como al descargar.
 */
class BUG20260923068Test extends TestCase
{
    use RefreshDatabase;

    private function crearCoInvestigadorGdiConGrupo(): array
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $depto = Department::create(['nombre' => 'Depto BUG-068']);
        $ciudad = City::create(['nombre' => 'Ciudad BUG-068', 'department_id' => $depto->id]);
        $centro = TrainingCenter::create([
            'nombre' => 'Centro BUG-068', 'codigo' => 'B068', 'activo' => true,
            'department_id' => $depto->id, 'city_id' => $ciudad->id,
        ]);

        $director = User::factory()->create(['training_center_id' => $centro->id]);
        $director->assignRole('director_grupo_investigacion');

        $grupo = GrupoInvestigacion::create([
            'training_center_id' => $centro->id,
            'creator_id' => $director->id,
            'director_id' => $director->id,
            'nombre' => 'Grupo BUG-068',
            'codigo' => 'G-B068',
            'estado' => EstadoEnum::Activo,
        ]);

        $coInv = User::factory()->create([
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => $grupo->id,
        ]);
        $coInv->assignRole('co_investigador_gdi');

        return [$coInv, $centro, $grupo];
    }

    public function test_store_guarda_el_nombre_original_del_archivo(): void
    {
        [$coInv, $centro] = $this->crearCoInvestigadorGdiConGrupo();
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea BUG-068']);

        $this->actingAs($coInv)->post(route('co-investigador-gdi.productos.store'), [
            'nombre' => 'Producto con archivo',
            'research_line_id' => $linea->id,
            'archivos' => [UploadedFile::fake()->create('informe final.pdf', 100)],
        ]);

        $producto = MincienciasProduct::firstOrFail();
        $archivo = $producto->files()->firstOrFail();

        $this->assertSame('informe final.pdf', $archivo->nombre_original);
        $this->assertNotSame('informe final.pdf', basename($archivo->archivo));
    }

    public function test_store_archivo_guarda_el_nombre_original(): void
    {
        [$coInv, $centro] = $this->crearCoInvestigadorGdiConGrupo();
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea BUG-068 B']);

        $producto = MincienciasProduct::create([
            'user_id' => $coInv->id,
            'training_center_id' => $centro->id,
            'grupo_investigacion_id' => $coInv->grupo_investigacion_id,
            'research_line_id' => $linea->id,
            'nombre' => 'Producto existente',
            'estado_revision' => 'pendiente',
        ]);

        $this->actingAs($coInv)->post(route('co-investigador-gdi.productos.archivos.store', $producto), [
            'archivo' => UploadedFile::fake()->create('anexo tecnico.docx', 50),
        ]);

        $archivo = $producto->files()->firstOrFail();
        $this->assertSame('anexo tecnico.docx', $archivo->nombre_original);
    }

    public function test_pantalla_muestra_nombre_original_en_vez_del_hash(): void
    {
        [$coInv, $centro] = $this->crearCoInvestigadorGdiConGrupo();
        $linea = ResearchLine::create(['training_center_id' => $centro->id, 'nombre' => 'Linea BUG-068 C']);

        $this->actingAs($coInv)->post(route('co-investigador-gdi.productos.store'), [
            'nombre' => 'Producto con archivo visible',
            'research_line_id' => $linea->id,
            'archivos' => [UploadedFile::fake()->create('reporte tecnico.pdf', 100)],
        ]);

        $producto = MincienciasProduct::firstOrFail();
        $archivo = $producto->files()->firstOrFail();

        $response = $this->actingAs($coInv)->get(route('co-investigador-gdi.productos.show', $producto));

        $response->assertOk();
        $response->assertSee('reporte tecnico.pdf');
        $response->assertDontSee(basename($archivo->archivo));
    }
}
