<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-20260813-045 — el código del semillero debe aceptar alfanumérico
 * (ej: "ADSO2026"), no solo números. Cambia seedlings.codigo de integer a
 * string. Verificado antes: los 3 registros existentes son numéricos, la
 * conversión no pierde datos.
 *
 * Usa Blueprint::change() — Laravel 12 lo soporta nativamente en MySQL y
 * SQLite sin doctrine/dbal (verificado: SQLiteGrammar/MySqlGrammar tienen
 * compileChange() propio). La primera versión de esta migración usaba SQL
 * crudo específico de MySQL y rompía la suite completa contra SQLite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seedlings', function (Blueprint $table) {
            $table->string('codigo', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('seedlings', function (Blueprint $table) {
            $table->integer('codigo')->change();
        });
    }
};
