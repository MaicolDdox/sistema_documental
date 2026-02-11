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

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->enum('document_type', ["cc","ti","passport","foreign_id"]);
            $table->string('document_number', 50)->unique();
            $table->enum('gender', ["male","female","other"])->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->foreignId('entity_position_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('engagement_type_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('training_center_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ["active","inactive","suspended"])->default('active');
            $table->index('status');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
