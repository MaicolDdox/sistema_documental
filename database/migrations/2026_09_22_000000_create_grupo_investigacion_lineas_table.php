<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivote grupo_investigacion <-> research_lines: un grupo puede tener
 * varias líneas de investigación asociadas (antes era una sola,
 * linea_investigacion_principal_id en grupos_investigacion).
 *
 * Nombre corto a propósito: "grupo_investigacion_research_lines" generaba
 * un nombre de constraint FK que excede el límite de 64 caracteres de MySQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_investigacion_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_investigacion_id')->constrained('grupos_investigacion')->cascadeOnDelete();
            $table->foreignId('research_line_id')->constrained('research_lines')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['grupo_investigacion_id', 'research_line_id'], 'gi_lineas_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_investigacion_lineas');
    }
};
