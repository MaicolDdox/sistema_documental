<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // INT max = 2,147,483,647 — celulares colombianos superan ese límite (ej. 3118220252)
            // Se cambia a bigInteger (unsigned) para soportar todos los formatos de celular
            $table->unsignedBigInteger('celular')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->integer('celular')->change();
        });
    }
};
