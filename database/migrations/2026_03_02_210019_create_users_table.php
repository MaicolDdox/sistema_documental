<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_center_id')
                ->comment('Centro de formación al que pertenece el usuario')
                ->nullable()
                ->constrained('training_centers')
                ->nullOnDelete();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('tipo_documento', [
                'documento identidad',
                'cedula ciudadana',
                'pasaporte',
                'cedula extrangera',
            ]);
            $table->integer('numero_documento');
            $table->string('password');
            $table->rememberToken();
            $table->enum('estado', ['activo', 'inactivo'])->default('inactivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
