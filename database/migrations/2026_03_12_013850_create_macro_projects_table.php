<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('macro_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_group_id')
                ->constrained('research_groups')
                ->onDelete('CASCADE');
            $table->string('codigo');
            $table->string('nombre');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('macro_projects');
    }
};
