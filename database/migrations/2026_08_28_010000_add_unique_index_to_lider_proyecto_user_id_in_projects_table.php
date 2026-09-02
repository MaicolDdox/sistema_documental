<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-20260813-036 (hallazgo de /code-review) — "cada líder de proyecto
 * lidera un solo proyecto" solo se validaba con Rule::unique() en
 * LiderSemillero\ProyectosController, sin índice único en BD. Dos
 * peticiones concurrentes asignando al mismo líder de proyecto podían
 * pasar la validación antes de que la primera terminara de guardar.
 *
 * MySQL permite múltiples NULL en una columna con índice único (un
 * proyecto sin líder de proyecto asignado todavía sigue siendo válido).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unique('lider_proyecto_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['lider_proyecto_user_id']);
        });
    }
};
