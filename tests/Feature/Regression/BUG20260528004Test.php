<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-004
 * Columnas opcionales de group_products definidas como NOT NULL causaban
 * SQLSTATE[23000] al registrar productos sin rellenar esos campos.
 * Corregido: 2026-05-28 — migración make_group_products_optional_fields_nullable
 * + fallback '0' para codigo_proyecto_origen en ProductoService.
 */
class BUG20260528004Test extends TestCase
{
    use RefreshDatabase;

    public function test_group_products_optional_fields_are_nullable(): void
    {
        $nullable = [
            'nombre_programa_formacion_impacto',
            'minciencias_typology_id',
            'minciencias_subcategory_id',
            'knowledge_grand_area_id',
            'knowledge_area_id',
            'campo_otro',
            'url_repositorio',
            'evidencia',
            'observaciones_revision',
            'descripcion',
        ];

        foreach ($nullable as $column) {
            $this->assertTrue(
                $this->columnIsNullable('group_products', $column),
                "La columna 'group_products.{$column}' debe ser nullable"
            );
        }
    }

    public function test_group_products_required_fields_are_not_null(): void
    {
        $required = [
            'author_id',
            'product_id',
            'tipo_proyecto_origen',
            'codigo_proyecto_origen',
            'titulo',
            'anio_publicacion',
            'tiene_repositorio',
            'autoriza_datos',
        ];

        foreach ($required as $column) {
            $this->assertFalse(
                $this->columnIsNullable('group_products', $column),
                "La columna 'group_products.{$column}' debe ser NOT NULL"
            );
        }
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
