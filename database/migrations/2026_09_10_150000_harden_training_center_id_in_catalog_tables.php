<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corre después de 2026_09_10_140000_backfill_training_center_id_in_catalog_tables.php,
 * que ya garantizó que ninguna fila quedó con training_center_id NULL.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tablas = [
        'entity_positions',
        'linkage_types',
        'training_program_types',
        'training_programs',
        'research_lines',
        'technological_lines',
        'thematic_areas',
        'project_modalities',
        'investigation_types',
        'minciencias_typologies',
        'minciencias_subcategories',
    ];

    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('training_center_id')->nullable(false)->change();
            });
        }

        // El unique() global de nombre ya se quitó en la migración de backfill
        // (2026_09_10_140000), antes de clonar filas por centro. Aquí solo se
        // agrega el nuevo compuesto.
        Schema::table('entity_positions', function (Blueprint $table) {
            $table->unique(['nombre', 'training_center_id']);
        });

        Schema::table('linkage_types', function (Blueprint $table) {
            $table->unique(['nombre', 'training_center_id']);
        });
    }

    public function down(): void
    {
        Schema::table('entity_positions', function (Blueprint $table) {
            $table->dropUnique(['nombre', 'training_center_id']);
        });

        Schema::table('linkage_types', function (Blueprint $table) {
            $table->dropUnique(['nombre', 'training_center_id']);
        });

        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('training_center_id')->nullable()->change();
            });
        }
    }
};
