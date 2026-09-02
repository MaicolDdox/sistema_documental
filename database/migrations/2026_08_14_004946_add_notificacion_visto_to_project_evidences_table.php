<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_evidences', function (Blueprint $table) {
            // Notificación tipo "punto rojo": null = hay una acción de revisión
            // sin ver por ese rol; se resetea a null en cada aprobación/rechazo
            // y se marca con now() cuando esa persona visita la página que
            // refleja el estado actual (ver RevisionEvidenciaService).
            $table->timestamp('visto_por_lider_proyecto_at')->nullable()->after('revisado_director_at');
            $table->timestamp('visto_por_lider_semillero_at')->nullable()->after('visto_por_lider_proyecto_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_evidences', function (Blueprint $table) {
            $table->dropColumn(['visto_por_lider_proyecto_at', 'visto_por_lider_semillero_at']);
        });
    }
};
