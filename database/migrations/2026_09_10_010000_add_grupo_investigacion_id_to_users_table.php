<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * co_investigador_gdi no tiene columna propia hoy que indique su grupo de
 * investigación. Se fija cuando director_grupo_investigacion crea la cuenta
 * (mismo patrón que training_center_id: dato fijo en users, heredado al
 * crear registros hijos como minciencias_products).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('grupo_investigacion_id')->nullable()->after('training_center_id')
                ->constrained('grupos_investigacion')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grupo_investigacion_id');
        });
    }
};
