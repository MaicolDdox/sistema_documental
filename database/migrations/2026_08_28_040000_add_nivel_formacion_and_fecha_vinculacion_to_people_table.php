<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-20260813-044 — "Nivel de Formación" y "Fecha de Vinculación", nuevos
 * campos exclusivos del formulario de perfil del co_investigador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('nivel_formacion')->nullable()->after('cvlac_link');
            $table->date('fecha_vinculacion')->nullable()->after('nivel_formacion');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn(['nivel_formacion', 'fecha_vinculacion']);
        });
    }
};
