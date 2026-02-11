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

        Schema::create('research_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('director_id')->constrained('users');
            $table->enum('decision', ["approved","rejected","requires_changes"]);
            $table->text('notes')->nullable();
            $table->dateTime('reviewed_at');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_product_reviews');
    }
};
