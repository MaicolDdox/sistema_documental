<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('CASCADE');
            $table->foreignId('entity_position_id')
                ->constrained('entity_positions')
                ->onDelete('CASCADE');
            $table->foreignId('linkage_type_id')
                ->constrained('linkage_types')
                ->onDelete('CASCADE');
            $table->foreignId('training_program_id')
                ->constrained('training_programs')
                ->onDelete('CASCADE');
            $table->string('primer_nombre');
            $table->string('segundo_nombre')->nullable();
            $table->string('primer_apellido');
            $table->string('segundo_apellido');
            $table->enum('genero', ['masculino', 'femenino', 'prefiero no decirlo']);
            $table->integer('telefono')->nullable();
            $table->integer('celular');
            $table->string('eps');
            $table->string('email_institucional')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
