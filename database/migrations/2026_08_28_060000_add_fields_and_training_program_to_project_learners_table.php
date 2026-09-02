<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-20260813-047 — formulario de registrar aprendiz (lider_proyecto):
 * agrega teléfono y correo electrónico; reemplaza el campo de texto libre
 * "nombre_tecnologo" por training_program_id, conectado al catálogo real
 * de Programas de Formación que administra administrador_sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_learners', function (Blueprint $table) {
            $table->string('telefono', 20)->nullable()->after('ficha');
            $table->string('email')->nullable()->after('telefono');
            $table->foreignId('training_program_id')->nullable()->after('email')
                ->constrained('training_programs')->nullOnDelete();
        });

        Schema::table('project_learners', function (Blueprint $table) {
            $table->dropColumn('nombre_tecnologo');
        });
    }

    public function down(): void
    {
        Schema::table('project_learners', function (Blueprint $table) {
            $table->string('nombre_tecnologo')->default('')->after('ficha');
        });

        Schema::table('project_learners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_program_id');
            $table->dropColumn(['telefono', 'email']);
        });
    }
};
