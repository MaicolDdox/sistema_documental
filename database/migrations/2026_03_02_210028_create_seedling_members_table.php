<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seedling_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedling_id')
                ->constrained('seedlings')
                ->onDelete('CASCADE');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('CASCADE');
            $table->timestamps();
            $table->unique(['seedling_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seedling_members');
    }
};
