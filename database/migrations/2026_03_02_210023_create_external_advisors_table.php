<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_advisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->comment('Nulo si el asesor no tiene cuenta en el sistema')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('nombre_completo');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('institucion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_advisors');
    }
};
