<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seedlings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')
                ->constrained('users')
                ->onDelete('CASCADE');
            $table->foreignId('leader_id')
                ->comment('Líder del semillero: administra la información, NO es autor de proyectos')
                ->constrained('users')
                ->onDelete('CASCADE');
            $table->foreignId('research_group_id')
                ->comment('Un semillero puede pertenecer a un grupo de investigación')
                ->nullable()
                ->constrained('research_groups')
                ->nullOnDelete();
            $table->string('nombre');
            $table->integer('codigo');
            $table->string('logo');
            $table->text('descripccion')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seedlings');
    }
};
