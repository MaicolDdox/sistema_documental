<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_groups', function (Blueprint $table) {
            // Cambiar de integer a string para permitir códigos como "AGT-001"
            $table->string('codigo', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('research_groups', function (Blueprint $table) {
            // Revertir a integer si se hace rollback
            $table->integer('codigo')->nullable(false)->change();
        });
    }
};

