<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hacer nullable los FK opcionales y fecha_inicio en projects
        Schema::table('projects', function (Blueprint $table) {
            // Las FK opcionales deben ser nullable para poder omitirlas en el form
            $table->foreignId('technological_line_id')->nullable()->change();
            $table->foreignId('thematic_area_id')->nullable()->change();
            $table->foreignId('project_modality_id')->nullable()->change();
            $table->foreignId('investigation_type_id')->nullable()->change();
            $table->date('fecha_inicio')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('investigation_type_id')->nullable(false)->change();
            $table->foreignId('technological_line_id')->nullable(false)->change();
            $table->foreignId('thematic_area_id')->nullable(false)->change();
            $table->foreignId('project_modality_id')->nullable(false)->change();
            $table->date('fecha_inicio')->nullable(false)->change();
        });
    }
};
