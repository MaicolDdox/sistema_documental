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
 * Regresión: BUG-20260825-001
 * Nueva capacidad para el rol co_investigador: registrar un "Producto
 * Minciencias" propio, 100% personal y privado — sin vincularlo a ningún
 * semillero, proyecto ni líder de proyecto.
 *
 * Cubre:
 * - Un co_investigador puede crear un producto Minciencias sin semillero/proyecto/líder.
 * - El producto NO aparece en el índice de otro co_investigador.
 * - Otro co_investigador recibe 403 al intentar ver/editar/eliminar por URL directa (ownership leak).
 * - Subida y eliminación de archivo adjunto respeta ownership.
 * - Eliminar el producto elimina también sus archivos asociados (sin huérfanos).
 */
class BUG20260825001Test extends TestCase
{
    use RefreshDatabase;

    private function crearCoinvestigador(): User
    {
        Role::firstOrCreate(['name' => 'co_investigador', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'training_center_id' => null,
            'estado' => EstadoEnum::Activo,
        ]);
        $user->assignRole('co_investigador');

        return $user;
    }

    private function crearLineaInvestigacion(): ResearchLine
    {
        return ResearchLine::firstOrCreate(['nombre' => 'Línea Test BUG-20260825-001']);
    }

    /**
     * BUG-20260813-029 volvió obligatorio training_center_id en
     * minciencias_products (el co-investigador elige el centro al crear el
     * producto, para que el administrador_sistema de ese centro lo apruebe).
     */
    private function crearCentro(): TrainingCenter
    {
        $depto = Department::firstOrCreate(['nombre' => 'Depto Test BUG-20260825-001']);
        $ciudad = City::firstOrCreate(['nombre' => 'Ciudad Test BUG-20260825-001', 'department_id' => $depto->id]);

        return TrainingCenter::firstOrCreate(
            ['nombre' => 'Centro Test BUG-20260825-001'],
            ['codigo' => 'T-825-001', 'activo' => true, 'department_id' => $depto->id, 'city_id' => $ciudad->id]
        );
    }

    public function test_co_investigador_puede_crear_producto_minciencias_sin_vinculos(): void
    {
        $coinvestigador = $this->crearCoinvestigador();
        $linea = $this->crearLineaInvestigacion();
        $centro = $this->crearCentro();

        $response = $this->actingAs($coinvestigador)->post(route('co-investigador.productos.store'), [
            'nombre' => 'Producto Minciencias Personal',
            'descripcion' => 'Descripción de prueba',
            'research_line_id' => $linea->id,
            'training_center_id' => $centro->id,
        ]);

        $producto = MincienciasProduct::first();

        $response->assertRedirect(route('co-investigador.productos.show', $producto));
        $this->assertNotNull($producto);
        $this->assertEquals($coinvestigador->id, $producto->user_id);
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
            'training_center_id' => $this->crearCentro()->id,
            'nombre' => 'Producto Solo Del Propietario',
            'estado' => EstadoEnum::Activo,
        ]);

        $response = $this->actingAs($otro)->get(route('co-investigador.productos.index'));

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
            'training_center_id' => $this->crearCentro()->id,
            'nombre' => 'Producto Ajeno',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->actingAs($otro)->get(route('co-investigador.productos.show', $producto))->assertStatus(403);
        $this->actingAs($otro)->get(route('co-investigador.productos.edit', $producto))->assertStatus(403);
        $this->actingAs($otro)->put(route('co-investigador.productos.update', $producto), [
            'nombre' => 'Hackeado',
            'research_line_id' => $linea->id,
        ])->assertStatus(403);
        $this->actingAs($otro)->delete(route('co-investigador.productos.destroy', $producto))->assertStatus(403);

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
            'training_center_id' => $this->crearCentro()->id,
            'nombre' => 'Producto Con Archivos',
            'estado' => EstadoEnum::Activo,
        ]);

        // Un tercero no puede subir archivos a un producto ajeno.
        $this->actingAs($otro)->post(route('co-investigador.productos.archivos.store', $producto), [
            'archivo' => UploadedFile::fake()->create('intruso.pdf', 100),
        ])->assertStatus(403);

        // El propietario sí puede subir el archivo.
        $response = $this->actingAs($propietario)->post(route('co-investigador.productos.archivos.store', $producto), [
            'archivo' => UploadedFile::fake()->create('soporte.pdf', 100),
            'descripcion' => 'Soporte del producto',
        ]);
        $response->assertRedirect(route('co-investigador.productos.show', $producto));

        $archivo = MincienciasProductFile::where('minciencias_product_id', $producto->id)->first();
        $this->assertNotNull($archivo);
        Storage::disk('public')->assertExists($archivo->archivo);

        // Un tercero no puede eliminar el archivo ajeno.
        $this->actingAs($otro)->delete(route('co-investigador.archivos.destroy', $archivo))->assertStatus(403);
        $this->assertDatabaseHas('minciencias_product_files', ['id' => $archivo->id]);

        // El propietario sí puede eliminarlo, y el archivo físico también se borra.
        $this->actingAs($propietario)->delete(route('co-investigador.archivos.destroy', $archivo))
            ->assertRedirect(route('co-investigador.productos.show', $producto));

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
            'training_center_id' => $this->crearCentro()->id,
            'nombre' => 'Producto A Eliminar',
            'estado' => EstadoEnum::Activo,
        ]);

        $this->actingAs($propietario)->post(route('co-investigador.productos.archivos.store', $producto), [
            'archivo' => UploadedFile::fake()->create('anexo.pdf', 100),
        ]);

        $archivo = MincienciasProductFile::where('minciencias_product_id', $producto->id)->first();
        $this->assertNotNull($archivo);

        $this->actingAs($propietario)->delete(route('co-investigador.productos.destroy', $producto))
            ->assertRedirect(route('co-investigador.productos.index'));

        $this->assertDatabaseMissing('minciencias_products', ['id' => $producto->id]);
        $this->assertDatabaseMissing('minciencias_product_files', ['id' => $archivo->id]);
        Storage::disk('public')->assertMissing($archivo->archivo);
    }
}
