<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_centers', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->smallInteger('codigo');
            $table->foreignId('department_id')
                ->constrained('departments')
                ->onDelete('CASCADE');
            $table->foreignId('city_id')
                ->constrained('cities')
                ->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_centers');
    }
};
