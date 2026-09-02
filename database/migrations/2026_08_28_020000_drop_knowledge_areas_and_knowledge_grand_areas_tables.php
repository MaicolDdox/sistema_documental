<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-20260813-041 — "Áreas del Conocimiento" se elimina por completo del
 * sistema (decisión del usuario). Ninguna tabla activa las referenciaba ya
 * (group_products, el único consumidor, se eliminó en la migración
 * 2026_08_13_140000 del rediseño de roles) y ambas tablas estaban vacías
 * (0 filas) al momento de esta migración — verificado antes de correr.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Hijo antes que padre (knowledge_areas.knowledge_grand_area_id).
        Schema::dropIfExists('knowledge_areas');
        Schema::dropIfExists('knowledge_grand_areas');
    }

    public function down(): void
    {
        Schema::create('knowledge_grand_areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_grand_area_id')->constrained('knowledge_grand_areas')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }
};
