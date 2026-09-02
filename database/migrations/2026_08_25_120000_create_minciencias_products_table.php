<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minciencias_products', function (Blueprint $table) {
            $table->id();

            // Dueño — co-investigador que registra el producto. Producto
            // 100% personal: no se vincula a semillero, proyecto ni líder.
            $table->foreignId('user_id')->constrained('users')->onDelete('CASCADE');

            $table->foreignId('research_line_id')->constrained('research_lines')->onDelete('CASCADE');
            $table->foreignId('technological_line_id')->nullable()->constrained('technological_lines')->nullOnDelete();
            $table->foreignId('thematic_area_id')->nullable()->constrained('thematic_areas')->nullOnDelete();
            $table->foreignId('project_modality_id')->nullable()->constrained('project_modalities')->nullOnDelete();
            $table->foreignId('investigation_type_id')->nullable()->constrained('investigation_types')->nullOnDelete();

            $table->string('nombre');
            $table->text('descripcion')->nullable();

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->string('estado')->default('activo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minciencias_products');
    }
};
