<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_groups', function (Blueprint $table) {
            // Permitir que el logo sea opcional
            $table->string('logo')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('research_groups', function (Blueprint $table) {
            // Revertir a requerido si se hace rollback
            $table->string('logo')->nullable(false)->change();
        });
    }
};

