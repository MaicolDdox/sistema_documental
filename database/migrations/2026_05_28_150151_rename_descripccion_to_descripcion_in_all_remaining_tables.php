<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
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

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'descripccion')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->renameColumn('descripccion', 'descripcion');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'descripcion')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->renameColumn('descripcion', 'descripccion');
                });
            }
        }
    }
};
