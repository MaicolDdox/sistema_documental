<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoEnum;
use App\Models\City;
use App\Models\Department;
use App\Models\MincienciasProduct;
use App\Models\MincienciasProductFile;
use App\Models\ResearchLine;
use App\Models\TrainingCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: BUG-20260825-001 (adaptado a la reforma de roles GDI/SDI).
 *
 * Capacidad original: co_investigador registraba un "Producto Minciencias"
 * propio, eligiendo el centro de formación en el formulario. Tras la
 * reforma, esa función quedó exclusivamente en co_investigador_gdi, que ya
 * NO elige el centro (se hereda de su propio usuario, ver BUG-20260813-029
 * reescrito). El resto de las reglas de ownership no cambió.
 */
class BUG20260825001Test extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(): TrainingCenter
    {
        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-20260825-001']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-20260825-001', 'department_id' => $depto->id]);

        return TrainingCenter::firstOrCreate(
            ['nombre' => 'Centro Test BUG-20260825-001'],
            ['codigo' => 'T-825-001', 'activo' => true, 'department_id' => $depto->id, 'city_id' => $ciudad->id]
        );
    }

    private function crearCoinvestigador(): User
    {
        Role::firstOrCreate(['name' => 'co_investigador_gdi', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'training_center_id' => $this->crearCentro()->id,
            'estado' => EstadoEnum::Activo,
        ]);
        $user->assignRole('co_investigador_gdi');

        return $user;
    }

    private function crearLineaInvestigacion(): ResearchLine
    {
        return ResearchLine::firstOrCreate([
            'nombre' => 'Línea Test BUG-20260825-001',
            'training_center_id' => $this->crearCentro()->id,
        ]);
    }

    public function test_co_investigador_gdi_puede_crear_producto_minciencias_sin_vinculos(): void
    {
        $coinvestigador = $this->crearCoinvestigador();
        $linea = $this->crearLineaInvestigacion();

        $response = $this->actingAs($coinvestigador)->post(route('co-investigador-gdi.productos.store'), [
            'nombre' => 'Producto Minciencias Personal',
            'descripcion' => 'Descripción de prueba',
            'research_line_id' => $linea->id,
        ]);

        $producto = MincienciasProduct::first();

        $response->assertRedirect(route('co-investigador-gdi.productos.show', $producto));
        $this->assertNotNull($producto);
        $this->assertEquals($coinvestigador->id, $producto->user_id);
        $this->assertEquals($coinvestigador->training_center_id, $producto->training_center_id);
        $this->assertDatabaseHas('minciencias_products', [
            'nombre' => 'Producto Minciencias Personal',
            'user_id' => $coinvestigador->id,
        ]);
    }

    public function test_producto_no_aparece_en_el_indice_de_otro_co_investigador(): void
    {
        $propietario = $this->crearCoinvestigador();
        $otro = $this->crearCoinvestigador();
        $linea = $this->crearLineaInvestigacion();

        $producto = MincienciasProduct::create([
            'user_id' => $propietario->id,
            'research_line_id' => $linea->id,
            'training_center_id' => $propietario->training_center_id,
            'nombre' => 'Producto Solo Del Propietario',
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($otro)->get(route('co-investigador-gdi.productos.index'));

        $response->assertStatus(200);
        $response->assertDontSee($producto->nombre);
    }

    public function test_otro_co_investigador_recibe_403_al_ver_editar_o_eliminar_producto_ajeno(): void
    {
        $propietario = $this->crearCoinvestigador();
        $otro = $this->crearCoinvestigador();
        $linea = $this->crearLineaInvestigacion();

        $producto = MincienciasProduct::create([
            'user_id' => $propietario->id,
            'research_line_id' => $linea->id,
            'training_center_id' => $propietario->training_center_id,
            'nombre' => 'Producto Ajeno',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->actingAs($otro)->get(route('co-investigador-gdi.productos.show', $producto))->assertStatus(403);
        $this->actingAs($otro)->get(route('co-investigador-gdi.productos.edit', $producto))->assertStatus(403);
        $this->actingAs($otro)->put(route('co-investigador-gdi.productos.update', $producto), [
            'nombre' => 'Hackeado',
            'research_line_id' => $linea->id,
        ])->assertStatus(403);
        $this->actingAs($otro)->delete(route('co-investigador-gdi.productos.destroy', $producto))->assertStatus(403);

        $this->assertDatabaseHas('minciencias_products', [
            'id' => $producto->id,
            'nombre' => 'Producto Ajeno',
        ]);
    }

    public function test_subida_y_eliminacion_de_archivo_respeta_ownership(): void
    {
        Storage::fake('public');

        $propietario = $this->crearCoinvestigador();
        $otro = $this->crearCoinvestigador();
        $linea = $this->crearLineaInvestigacion();

        $producto = MincienciasProduct::create([
            'user_id' => $propietario->id,
            'research_line_id' => $linea->id,
            'training_center_id' => $propietario->training_center_id,
            'nombre' => 'Producto Con Archivos',
            'estado' => EstadoEnum::Activo,
        ]);

        // Un tercero no puede subir archivos a un producto ajeno.
        $this->actingAs($otro)->post(route('co-investigador-gdi.productos.archivos.store', $producto), [
            'archivo' => UploadedFile::fake()->create('intruso.pdf', 100),
        ])->assertStatus(403);

        // El propietario sí puede subir el archivo.
        $response = $this->actingAs($propietario)->post(route('co-investigador-gdi.productos.archivos.store', $producto), [
            'archivo' => UploadedFile::fake()->create('soporte.pdf', 100),
            'descripcion' => 'Soporte del producto',
        ]);
        $response->assertRedirect(route('co-investigador-gdi.productos.show', $producto));

        $archivo = MincienciasProductFile::where('minciencias_product_id', $producto->id)->first();
        $this->assertNotNull($archivo);
        Storage::disk('public')->assertExists($archivo->archivo);

        // Un tercero no puede eliminar el archivo ajeno.
        $this->actingAs($otro)->delete(route('co-investigador-gdi.archivos.destroy', $archivo))->assertStatus(403);
        $this->assertDatabaseHas('minciencias_product_files', ['id' => $archivo->id]);

        // El propietario sí puede eliminarlo, y el archivo físico también se borra.
        $this->actingAs($propietario)->delete(route('co-investigador-gdi.archivos.destroy', $archivo))
            ->assertRedirect(route('co-investigador-gdi.productos.show', $producto));

        $this->assertDatabaseMissing('minciencias_product_files', ['id' => $archivo->id]);
        Storage::disk('public')->assertMissing($archivo->archivo);
    }

    public function test_eliminar_producto_elimina_tambien_sus_archivos_asociados(): void
    {
        Storage::fake('public');

        $propietario = $this->crearCoinvestigador();
        $linea = $this->crearLineaInvestigacion();

        $producto = MincienciasProduct::create([
            'user_id' => $propietario->id,
            'research_line_id' => $linea->id,
            'training_center_id' => $propietario->training_center_id,
            'nombre' => 'Producto A Eliminar',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->actingAs($propietario)->post(route('co-investigador-gdi.productos.archivos.store', $producto), [
            'archivo' => UploadedFile::fake()->create('anexo.pdf', 100),
        ]);

        $archivo = MincienciasProductFile::where('minciencias_product_id', $producto->id)->first();
        $this->assertNotNull($archivo);

        $this->actingAs($propietario)->delete(route('co-investigador-gdi.productos.destroy', $producto))
            ->assertRedirect(route('co-investigador-gdi.productos.index'));

        $this->assertDatabaseMissing('minciencias_products', ['id' => $producto->id]);
        $this->assertDatabaseMissing('minciencias_product_files', ['id' => $archivo->id]);
        Storage::disk('public')->assertMissing($archivo->archivo);
    }
}
