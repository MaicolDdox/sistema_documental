<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minciencias_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('minciencias_typology_id')
                ->constrained('minciencias_typologies')
                ->onDelete('CASCADE');
            $table->string('nombre');
            $table->text('descripccion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minciencias_subcategories');
    }
};
