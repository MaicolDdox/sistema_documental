<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_product_id')
                ->constrained('group_products')
                ->cascadeOnDelete();
            $table->foreignId('reviewer_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('research_group_id')
                ->constrained('research_groups')
                ->restrictOnDelete();
            $table->enum('accion', ['aprobado', 'rechazado', 'en_revision']);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_product_reviews');
    }
};
