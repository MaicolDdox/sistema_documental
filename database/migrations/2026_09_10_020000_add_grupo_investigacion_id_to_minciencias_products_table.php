<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reforma GDI/SDI: el producto Minciencias deja de ser 100% personal y queda
 * vinculado al grupo de investigación del co_investigador_gdi que lo crea.
 * training_center_id no cambia de esquema (ya nullable desde BUG-20260813-029);
 * solo cambia el origen del dato: se hereda de Auth::user(), ya no se elige
 * en el formulario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('minciencias_products', function (Blueprint $table) {
            $table->foreignId('grupo_investigacion_id')->nullable()->after('training_center_id')
                ->constrained('grupos_investigacion')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('minciencias_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grupo_investigacion_id');
        });
    }
};
