<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hacer nullable la columna descripccion
        Schema::table('project_evidences', function (Blueprint $table) {
            $table->text('descripccion')->nullable()->change();
        });

        // Agregar columna `nombre` si no existe
        if (!Schema::hasColumn('project_evidences', 'nombre')) {
            Schema::table('project_evidences', function (Blueprint $table) {
                $table->string('nombre')->nullable()->after('archivo');
            });
        }

        // Agregar columna `uploaded_by` si no existe
        if (!Schema::hasColumn('project_evidences', 'uploaded_by')) {
            Schema::table('project_evidences', function (Blueprint $table) {
                $table->unsignedBigInteger('uploaded_by')->nullable()->after('nombre');
            });
        }
    }

    public function down(): void
    {
        Schema::table('project_evidences', function (Blueprint $table) {
            $table->text('descripccion')->nullable(false)->change();
        });
    }
};
