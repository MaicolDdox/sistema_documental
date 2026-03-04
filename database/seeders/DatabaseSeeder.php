<?php

namespace Database\Seeders;

use App\Enums\EstadoEnum;
use App\Enums\TipoDocumentoEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ─── Crear cadena mínima de datos de referencia ──────────────
        $entityPositionId = DB::table('entity_positions')->insertGetId([
            'nombre' => 'Administrador del Sistema',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $linkageTypeId = DB::table('linkage_types')->insertGetId([
            'nombre' => 'Planta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $trainingRecordId = DB::table('training_records')->insertGetId([
            'codigo' => 1001,
            'descripccion' => 'Registro Calificado General',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $trainingProgramTypeId = DB::table('training_program_types')->insertGetId([
            'nombre' => 'Tecnólogo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $trainingProgramId = DB::table('training_programs')->insertGetId([
            'training_record_id' => $trainingRecordId,
            'training_program_type_id' => $trainingProgramTypeId,
            'nombre' => 'ADSO',
            'jornada' => 'diurna',
            'modalidad' => 'presencial',
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ─── Crear usuario admin de prueba ───────────────────────────
        $userId = DB::table('users')->insertGetId([
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'tipo_documento' => TipoDocumentoEnum::CedulaCiudadana->value,
            'numero_documento' => 12345678,
            'password' => Hash::make('password'),
            'estado' => EstadoEnum::Activo->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ─── Crear persona asociada ──────────────────────────────────
        DB::table('people')->insert([
            'user_id' => $userId,
            'entity_position_id' => $entityPositionId,
            'linkage_type_id' => $linkageTypeId,
            'training_program_id' => $trainingProgramId,
            'primer_nombre' => 'Admin',
            'segundo_nombre' => 'Del',
            'primer_apellido' => 'Sistema',
            'segundo_apellido' => 'SENA',
            'genero' => 'masculino',
            'telefono' => 1234567,
            'celular' => 300123456,
            'eps' => 'Sanitas',
            'email_institucional' => 'admin@sena.edu.co',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
