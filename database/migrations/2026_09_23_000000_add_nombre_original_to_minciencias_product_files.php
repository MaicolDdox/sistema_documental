<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-20260923-068 — los archivos de productos Minciencias se mostraban con
 * el nombre aleatorio que Laravel usa para guardarlos en disco (p. ej.
 * "5AO6589RKir1QYSDeNcCnA6UDpFmjW0UpCLLYr2s.txt") en vez del nombre original
 * con el que el usuario los subió.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('minciencias_product_files', function (Blueprint $table) {
            $table->string('nombre_original')->nullable()->after('archivo');
        });
    }

    public function down(): void
    {
        Schema::table('minciencias_product_files', function (Blueprint $table) {
            $table->dropColumn('nombre_original');
        });
    }
};
