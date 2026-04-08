<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar teléfono y celular a cadenas para evitar desbordes numéricos
        Schema::table('people', function (Blueprint $table) {
            $table->string('telefono', 20)->nullable()->change();
            $table->string('celular', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->integer('telefono')->nullable()->change();
            $table->integer('celular')->change();
        });
    }
};

