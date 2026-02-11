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

        Schema::create('project_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedbed_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('evidence_name', 200)->nullable();
            $table->string('file_path', 500);
            $table->enum('file_type', ["pdf","excel","word","image","video","other"]);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_evidences');
    }
};
