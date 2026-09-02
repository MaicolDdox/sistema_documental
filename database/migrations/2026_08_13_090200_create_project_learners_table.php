<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aprendices como DATOS del proyecto, no como usuarios del sistema
 * (rediseño de roles). Ficha y nombre de tecnólogo son texto libre,
 * digitados por el Líder de Proyecto — no referencian los catálogos
 * training_records/training_programs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_learners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre_completo');
            $table->string('numero_documento');
            $table->string('ficha');
            $table->string('nombre_tecnologo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_learners');
    }
};
