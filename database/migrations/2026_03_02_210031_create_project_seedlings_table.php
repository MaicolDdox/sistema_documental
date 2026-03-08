<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_seedlings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedling_id')
                ->constrained('seedlings')
                ->onDelete('CASCADE');
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('CASCADE');
            $table->timestamps();
            $table->unique(['seedling_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_seedlings');
    }
};
