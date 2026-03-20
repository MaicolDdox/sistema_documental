<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Vincular asesorsem@sena.edu.co al primer semillero existente, si ambos existen.
        $asesorUser = DB::table('users')->where('email', 'asesorsem@sena.edu.co')->first();
        $semillero  = DB::table('seedlings')->orderBy('id')->first();

        if (!$asesorUser || !$semillero) {
            return;
        }

        // Obtener/crear registro en external_advisors para este usuario
        $external = DB::table('external_advisors')->where('user_id', $asesorUser->id)->first();
        if (!$external) {
            $externalId = DB::table('external_advisors')->insertGetId([
                'user_id'         => $asesorUser->id,
                'nombre_completo' => $asesorUser->email,
                'email'           => $asesorUser->email,
                'telefono'        => '',
                'institucion'     => 'SENA',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } else {
            $externalId = $external->id;
        }

        // Crear vínculo en seedling_advisors si no existe
        $exists = DB::table('seedling_advisors')
            ->where('seedling_id', $semillero->id)
            ->where('external_advisor_id', $externalId)
            ->exists();

        if (!$exists) {
            // La columna activo puede no existir en todos los entornos; la incluimos solo si está presente
            $columns = DB::getSchemaBuilder()->getColumnListing('seedling_advisors');
            $data = [
                'seedling_id'        => $semillero->id,
                'external_advisor_id'=> $externalId,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
            if (in_array('activo', $columns, true)) {
                $data['activo'] = true;
            }

            DB::table('seedling_advisors')->insert($data);
        }
    }

    public function down(): void
    {
        // No deshacemos el vínculo automáticamente para no borrar datos configurados por el usuario.
    }
};

