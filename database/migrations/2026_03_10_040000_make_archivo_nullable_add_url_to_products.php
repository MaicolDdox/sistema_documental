<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Permite null cuando el producto solo tiene URL externa (Drive, GitHub, etc.)
            $table->string('archivo')->nullable()->change();
            // Agrega URL de repositorio/enlace externo
            $table->string('url_repositorio', 500)->nullable()->after('archivo');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('url_repositorio')->nullable(); // keep it safe on rollback
            $table->string('archivo')->nullable(false)->change();
        });
    }
};
