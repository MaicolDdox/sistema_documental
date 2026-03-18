<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('tipo_financiacion', ['capacidad_instalada', 'financiado', 'con_alianza'])
                  ->nullable()
                  ->after('vinculacion_macro_proyecto')
                  ->comment('Tipo de financiación del proyecto');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('tipo_financiacion');
        });
    }
};
