<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_group_id')
                ->constrained('research_groups')
                ->onDelete('CASCADE');
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('CASCADE');
            $table->enum('tipo_participacion', ['origen', 'aliado', 'cooperacion'])
                ->default('origen')
                ->comment('origen = creado aquí; aliado/cooperacion = participación externa');
            $table->timestamps();
            $table->unique(['research_group_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_groups');
    }
};
