<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('research_product_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('email', 150)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->integer('author_order');
            $table->unique(['research_product_id', 'author_order']);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_product_authors');
    }
};
