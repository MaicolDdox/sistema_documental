<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos_investigacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_center_id')->constrained('training_centers')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('director_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('nombre');
            $table->string('codigo');
            $table->string('logo')->nullable();
            $table->text('descripcion')->nullable();
            $table->foreignId('linea_investigacion_principal_id')->nullable()
                ->constrained('research_lines')->nullOnDelete();

            $table->string('estado')->default('activo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos_investigacion');
    }
};
