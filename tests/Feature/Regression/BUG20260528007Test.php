<?php

namespace Tests\Feature\Regression;

use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoProyectoOrigenEnum;
use App\Models\GroupProduct;
use App\Models\Product;
use App\Models\ProductEvidence;
use App\Models\User;
use App\Services\Investigador\EvidenciaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-007
 * product_evidences.descripcion era NOT NULL sin default. EvidenciaService::subirParaProducto()
 * no pasaba ese campo causando SQLSTATE[HY000]: 1364 Field doesn't have a default value.
 * Corregido: 2026-05-28 — migración nullable + 'descripcion' => null en el service.
 */
class BUG20260528007Test extends TestCase
{
    use RefreshDatabase;

    public function test_product_evidences_descripcion_is_nullable(): void
    {
        $this->assertTrue(
            $this->columnIsNullable('product_evidences', 'descripcion'),
            "La columna 'product_evidences.descripcion' debe ser nullable"
        );
    }

    public function test_subir_evidencia_para_producto_no_lanza_excepcion_por_descripcion(): void
    {
        Storage::fake('local');

        $author = User::factory()->create();

        $product = Product::create([
            'nombre'          => 'Producto test',
            'estado'          => 'activo',
            'estado_revision' => EstadoRevisionEnum::Pendiente,
        ]);

        $groupProduct = GroupProduct::create([
            'author_id'              => $author->id,
            'product_id'             => $product->id,
            'tipo_proyecto_origen'   => TipoProyectoOrigenEnum::Semilleros,
            'codigo_proyecto_origen' => '0',
            'titulo'                 => 'Test BUG-007',
            'anio_publicacion'       => 2024,
            'tiene_repositorio'      => false,
            'autoriza_datos'         => true,
            'estado_revision'        => EstadoRevisionEnum::Pendiente,
        ]);

        $file = UploadedFile::fake()->create('evidencia.pdf', 100, 'application/pdf');

        $service = new EvidenciaService();
        $evidence = $service->subirParaProducto($file, $groupProduct, $author->id);

        $this->assertInstanceOf(ProductEvidence::class, $evidence);
        $this->assertNull($evidence->descripcion);
        $this->assertEquals($product->id, $evidence->product_id);
    }

    private function columnIsNullable(string $table, string $column): bool
    {
        $cols = \Illuminate\Support\Facades\DB::select(
            "PRAGMA table_info({$table})"
        );

        foreach ($cols as $col) {
            if ($col->name === $column) {
                return $col->notnull === 0;
            }
        }

        return false;
    }
}
