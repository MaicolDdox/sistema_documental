<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-20260813-042 — se elimina por completo del formulario de crear/editar
 * programas de formación la opción de "Ficha" (que auto-creaba filas en el
 * catálogo training_records) y "Jornada". Ninguna otra tabla del sistema
 * referenciaba training_record_id ni jornada — ambas eran exclusivas de
 * training_programs. Verificado antes: 0 filas en training_programs y
 * training_records.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('training_record_id');
            $table->dropColumn('jornada');
        });

        Schema::dropIfExists('training_records');
    }

    public function down(): void
    {
        Schema::create('training_records', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::table('training_programs', function (Blueprint $table) {
            $table->foreignId('training_record_id')->after('id')
                ->constrained('training_records')->onDelete('CASCADE');
            $table->enum('jornada', ['diurna', 'nocturna', 'presencial'])->after('descripcion');
        });
    }
};
