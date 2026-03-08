<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('CASCADE');
            $table->foreignId('user_id')
                ->comment('Aprendiz/autor del proyecto')
                ->constrained('users')
                ->onDelete('CASCADE');
            $table->boolean('activo')
                ->default(true)
                ->comment('false = ya terminó pero permanece en el historial del proyecto');
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_authors');
    }
};
