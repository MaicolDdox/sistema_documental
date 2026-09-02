<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minciencias_product_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('minciencias_product_id')->constrained('minciencias_products')->onDelete('CASCADE');

            $table->string('archivo')->nullable();
            $table->string('url_archivo')->nullable();
            $table->text('descripcion')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minciencias_product_files');
    }
};
