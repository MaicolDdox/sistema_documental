<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar la tabla ineficiente antigua
        Schema::dropIfExists('macro_project_linkages');

        // 2. Añadir la FK real hacia el catálogo a la tabla projects
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('macro_project_id')
                  ->nullable()
                  ->after('vinculacion_macro_proyecto')
                  ->constrained('macro_projects')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Revertir FK
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['macro_project_id']);
            $table->dropColumn('macro_project_id');
        });

        // Restaurar tabla antigua por si acaso (rollback)
        Schema::create('macro_project_linkages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('CASCADE');
            $table->foreignId('research_group_id')->constrained('research_groups')->onDelete('CASCADE');
            $table->integer('codigo');
            $table->string('nombre');
            $table->timestamps();
        });
    }
};
