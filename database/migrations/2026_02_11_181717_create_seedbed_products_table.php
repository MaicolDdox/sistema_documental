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

        Schema::create('seedbed_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedbed_project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 300);
            $table->enum('product_type', ["article","presentation","prototype","software","certificate","patent","registration","other"]);
            $table->text('description')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->date('obtained_on')->nullable();
            $table->enum('status', ["in_progress","completed","published"])->default('in_progress');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seedbed_products');
    }
};
