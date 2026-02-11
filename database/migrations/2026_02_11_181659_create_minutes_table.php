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

        Schema::create('minutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedbed_id')->constrained()->cascadeOnDelete();
            $table->string('document_name', 200)->nullable();
            $table->string('file_path', 500);
            $table->text('description')->nullable();
            $table->date('document_date')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minutes');
    }
};
