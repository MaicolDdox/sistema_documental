<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            // Cambiamos descripccion a TEXT para permitir descripciones largas
            $table->text('descripccion')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            // Revertir a string si se hace rollback (puede truncar datos largos)
            $table->string('descripccion')->nullable()->change();
        });
    }
};

