<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogos por centro: nullable por ahora, se vuelve NOT NULL en
 * 2026_09_10_150000_harden_training_center_id_in_catalog_tables.php
 * después de que la migración de datos rellene todas las filas existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entity_positions', function (Blueprint $table) {
            $table->foreignId('training_center_id')->nullable()->after('id')
                ->constrained('training_centers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('entity_positions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_center_id');
        });
    }
};
