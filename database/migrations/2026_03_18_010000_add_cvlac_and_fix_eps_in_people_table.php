<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // Asegurar que eps sea nullable (fix del error al crear investigador)
            $table->string('eps')->nullable()->change();

            // Nuevo campo para el enlace CvLAC del investigador
            if (! Schema::hasColumn('people', 'cvlac_link')) {
                $table->string('cvlac_link', 500)->nullable()->after('eps');
            }
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            if (Schema::hasColumn('people', 'cvlac_link')) {
                $table->dropColumn('cvlac_link');
            }
        });
    }
};
