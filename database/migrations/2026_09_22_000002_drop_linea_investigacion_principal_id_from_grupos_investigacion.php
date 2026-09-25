<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corre después de 2026_09_22_000001_backfill_grupo_investigacion_research_lines.php,
 * que ya copió los valores existentes al pivote grupo_investigacion_research_lines.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos_investigacion', function (Blueprint $table) {
            $table->dropConstrainedForeignId('linea_investigacion_principal_id');
        });
    }

    public function down(): void
    {
        Schema::table('grupos_investigacion', function (Blueprint $table) {
            $table->foreignId('linea_investigacion_principal_id')->nullable()
                ->after('descripcion')
                ->constrained('research_lines')->nullOnDelete();
        });
    }
};
