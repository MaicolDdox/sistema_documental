<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requiere redefinir el ENUM completo para añadir un valor
        DB::statement("ALTER TABLE `research_group_users` MODIFY `rol` ENUM('director', 'investigador_lider', 'investigador_asociado', 'integrante') NOT NULL DEFAULT 'integrante'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `research_group_users` MODIFY `rol` ENUM('investigador_lider', 'investigador_asociado', 'integrante') NOT NULL DEFAULT 'integrante'");
    }
};
