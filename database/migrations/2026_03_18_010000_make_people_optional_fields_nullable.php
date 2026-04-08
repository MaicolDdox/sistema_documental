<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // Hacer opcionales varios campos para permitir crear investigadores
            // con datos mínimos desde el módulo del director.
            $table->foreignId('entity_position_id')->nullable()->change();
            $table->foreignId('linkage_type_id')->nullable()->change();
            $table->foreignId('training_program_id')->nullable()->change();
            $table->enum('genero', ['masculino', 'femenino', 'prefiero no decirlo'])->nullable()->change();
            // Cambiar a string y permitir null; hay números largos existentes que no caben en int
            $table->string('celular', 20)->nullable()->change();
            $table->string('eps')->nullable()->change();
            $table->string('email_institucional')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // Revertir: volver a requeridos (sin null). Ten en cuenta que esto
            // puede fallar si ya existen registros con NULL.
            $table->foreignId('entity_position_id')->nullable(false)->change();
            $table->foreignId('linkage_type_id')->nullable(false)->change();
            $table->foreignId('training_program_id')->nullable(false)->change();
            $table->enum('genero', ['masculino', 'femenino', 'prefiero no decirlo'])->nullable(false)->change();
            $table->integer('celular')->nullable(false)->change();
            $table->string('eps')->nullable(false)->change();
            $table->string('email_institucional')->nullable(false)->change();
        });
    }
};

