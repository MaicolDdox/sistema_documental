<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_record_id')
                ->constrained('training_records')
                ->onDelete('CASCADE');
            $table->foreignId('training_program_type_id')
                ->constrained('training_program_types')
                ->onDelete('CASCADE');
            $table->string('nombre');
            $table->string('descripccion')->nullable();
            $table->enum('jornada', ['diurna', 'nocturna', 'presencial']);
            $table->enum('modalidad', ['virtual', 'presencial']);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_programs');
    }
};
