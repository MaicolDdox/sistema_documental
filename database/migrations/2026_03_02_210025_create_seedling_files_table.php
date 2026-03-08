<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seedling_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedling_id')
                ->constrained('seedlings')
                ->onDelete('CASCADE');
            $table->string('archivo');
            $table->string('url_archivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seedling_files');
    }
};
