<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Copia la línea de investigación principal (única) de cada grupo hacia
 * el pivote nuevo grupo_investigacion_lineas, antes de eliminar
 * la columna linea_investigacion_principal_id en la migración siguiente.
 */
return new class extends Migration
{
    public function up(): void
    {
        $grupos = DB::table('grupos_investigacion')
            ->whereNotNull('linea_investigacion_principal_id')
            ->get(['id', 'linea_investigacion_principal_id']);

        foreach ($grupos as $grupo) {
            DB::table('grupo_investigacion_lineas')->insertOrIgnore([
                'grupo_investigacion_id' => $grupo->id,
                'research_line_id' => $grupo->linea_investigacion_principal_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Irreversible: no se restaura la columna eliminada en la migración
        // siguiente, así que no hay a dónde devolver estos datos.
    }
};
