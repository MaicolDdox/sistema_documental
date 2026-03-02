<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('CASCADE');
            $table->foreignId('project_author_id')
                ->comment('Referencia al autor ya registrado en el proyecto')
                ->constrained('project_authors')
                ->onDelete('CASCADE');
            $table->timestamps();
            $table->unique(['product_id', 'project_author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_authors');
    }
};
