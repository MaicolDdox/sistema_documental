<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seedling_internal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seedling_id')->constrained('seedlings')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->string('tipo', 50)->default('otro'); // acta, informe, otro
            $table->string('url_archivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seedling_internal_documents');
    }
};
