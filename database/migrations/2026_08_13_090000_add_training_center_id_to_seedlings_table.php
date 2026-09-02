<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hoy el aislamiento por centro de un semillero depende enteramente de
 * seedlings.research_group_id -> research_groups.training_center_id.
 * Como "Grupo de Investigación" desaparece del sistema, el semillero
 * necesita su propio training_center_id directo. research_group_id se
 * deja intacto por ahora (todavía hay controladores que lo usan) y se
 * elimina en una migración posterior junto con research_groups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seedlings', function (Blueprint $table) {
            $table->foreignId('training_center_id')
                ->nullable()
                ->after('id')
                ->constrained('training_centers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('seedlings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_center_id');
        });
    }
};
