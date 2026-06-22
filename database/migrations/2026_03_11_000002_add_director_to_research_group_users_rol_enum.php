<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_group_users', function (Blueprint $table) {
            $table->enum('rol', ['director', 'investigador_lider', 'investigador_asociado', 'integrante'])
                  ->default('integrante')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('research_group_users', function (Blueprint $table) {
            $table->enum('rol', ['investigador_lider', 'investigador_asociado', 'integrante'])
                  ->default('integrante')
                  ->change();
        });
    }
};
