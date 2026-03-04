<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_center_id')
                ->nullable()
                ->constrained('training_centers')
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
        Schema::dropIfExists('research_groups');
    }
};
