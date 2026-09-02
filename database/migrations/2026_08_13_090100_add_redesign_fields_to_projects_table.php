<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Un proyecto pertenece a UN semillero (antes era many-to-many vía project_seedlings).
            $table->foreignId('seedling_id')
                ->nullable()
                ->after('project_creator_id')
                ->constrained('seedlings')
                ->nullOnDelete();

            // Asignado por el Líder de Semillero al crear el proyecto.
            $table->foreignId('lider_proyecto_user_id')
                ->nullable()
                ->after('seedling_id')
                ->constrained('users')
                ->nullOnDelete();

            // Mudado desde group_products.tipo_proyecto_origen (esa tabla desaparece).
            $table->string('tipo_proyecto_origen')->nullable()->after('tipo_financiacion');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seedling_id');
            $table->dropConstrainedForeignId('lider_proyecto_user_id');
            $table->dropColumn('tipo_proyecto_origen');
        });
    }
};
