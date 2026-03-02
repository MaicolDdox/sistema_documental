<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_group_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_group_id')
                ->constrained('research_groups')
                ->onDelete('CASCADE');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('CASCADE');
            $table->enum('rol', ['investigador_lider', 'investigador_asociado', 'integrante'])
                ->default('integrante');
            $table->timestamps();
            $table->unique(['research_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_group_users');
    }
};
