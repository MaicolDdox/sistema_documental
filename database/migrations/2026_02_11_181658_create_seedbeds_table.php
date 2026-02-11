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

        Schema::create('seedbeds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('seedbed_code', 50)->nullable()->unique();
            $table->string('logo_path', 255)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('director_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('training_center_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ["active","inactive","under_review"])->default('active');
            $table->date('founded_on')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seedbeds');
    }
};
