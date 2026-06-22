<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regresión: BUG-20260528-003
 * El typo 'descripccion' (doble c) estaba presente en 20 tablas del sistema,
 * causando SQLSTATE[42S22] al insertar o actualizar registros con ese campo.
 * Corregido: 2026-05-28 — migraciones de renombrado por tabla.
 */
class BUG20260528003Test extends TestCase
{
    use RefreshDatabase;

    private array $tables = [
        'projects',
        'group_products',
        'entity_positions',
        'linkage_types',
        'training_records',
        'training_program_types',
        'training_programs',
        'research_lines',
        'technological_lines',
        'thematic_areas',
        'project_modalities',
        'investigation_types',
        'minciencias_typologies',
        'minciencias_subcategories',
        'knowledge_grand_areas',
        'knowledge_areas',
        'research_groups',
        'seedlings',
        'project_evidences',
        'product_evidences',
    ];

    public function test_no_table_has_the_descripccion_typo(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->assertFalse(
                Schema::hasColumn($table, 'descripccion'),
                "La tabla '{$table}' aún tiene el typo 'descripccion' (doble c)"
            );
        }
    }

    public function test_all_affected_tables_have_descripcion_column(): void
    {
        $tablesWithDescripcion = [
            'projects',
            'group_products',
            'research_groups',
            'seedlings',
            'project_evidences',
            'product_evidences',
            'research_lines',
            'technological_lines',
            'thematic_areas',
            'project_modalities',
            'investigation_types',
            'minciencias_typologies',
            'minciencias_subcategories',
            'knowledge_grand_areas',
            'knowledge_areas',
        ];

        foreach ($tablesWithDescripcion as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->assertTrue(
                Schema::hasColumn($table, 'descripcion'),
                "La tabla '{$table}' no tiene la columna 'descripcion'"
            );
        }
    }
}
