<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_creator_id')
                ->comment('Usuario que registra el proyecto (líder de semillero o investigador)')
                ->constrained('users')
                ->onDelete('CASCADE');
            $table->foreignId('research_line_id')
                ->constrained('research_lines')
                ->onDelete('CASCADE');
            $table->foreignId('technological_line_id')
                ->constrained('technological_lines')
                ->onDelete('CASCADE');
            $table->foreignId('thematic_area_id')
                ->constrained('thematic_areas')
                ->onDelete('CASCADE');
            $table->foreignId('project_modality_id')
                ->constrained('project_modalities')
                ->onDelete('CASCADE');
            $table->foreignId('investigation_type_id')
                ->constrained('investigation_types')
                ->onDelete('CASCADE');
            $table->string('nombre');
            $table->text('descripccion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->boolean('vinculacion_macro_proyecto')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
