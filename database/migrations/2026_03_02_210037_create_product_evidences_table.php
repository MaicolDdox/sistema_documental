<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('CASCADE');
            $table->string('archivo')->nullable();
            $table->string('url_archivo')->nullable();
            $table->text('descripccion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_evidences');
    }
};
