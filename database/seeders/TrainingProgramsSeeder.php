<?php

namespace Database\Seeders;

use App\Models\TrainingProgramType;
use App\Models\TrainingRecord;
use App\Models\TrainingProgram;
use App\Enums\EstadoEnum;
use App\Enums\JornadaEnum;
use App\Enums\ModalidadEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingProgramsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Tipos de Programa (si no existen)
        $tipos = [
            ['id' => 1, 'nombre' => 'Tecnólogo', 'descripccion' => 'Programas de nivel tecnológico.'],
            ['id' => 2, 'nombre' => 'Técnico', 'descripccion' => 'Programas de nivel técnico.'],
            ['id' => 3, 'nombre' => 'Especialización Tecnológica', 'descripccion' => 'Especialización nivel tecnológico.'],
        ];

        foreach ($tipos as $tipo) {
            TrainingProgramType::updateOrCreate(['id' => $tipo['id']], $tipo);
        }

        // 2. Crear Fichas de Formación (TrainingRecord) dummy
        $fichas = [
            ['id' => 1, 'codigo' => '2502601', 'descripccion' => 'Ficha ADSO Diurna'],
            ['id' => 2, 'codigo' => '2603450', 'descripccion' => 'Ficha Gestión Virtual'],
        ];

        foreach ($fichas as $ficha) {
            TrainingRecord::updateOrCreate(['id' => $ficha['id']], $ficha);
        }

        // 3. Crear Programas de Formación
        $programas = [
            [
                'id' => 1,
                'training_record_id' => 1,
                'training_program_type_id' => 1,
                'nombre' => 'Análisis y Desarrollo de Software',
                'descripccion' => 'ADSO',
                'jornada' => JornadaEnum::Diurna->value,
                'modalidad' => ModalidadEnum::Presencial->value,
                'estado' => EstadoEnum::Activo->value,
            ],
            [
                'id' => 2,
                'training_record_id' => 2,
                'training_program_type_id' => 1,
                'nombre' => 'Gestión Administrativa',
                'descripccion' => 'Gestión Administrativa',
                'jornada' => JornadaEnum::Nocturna->value,
                'modalidad' => ModalidadEnum::Virtual->value,
                'estado' => EstadoEnum::Activo->value,
            ]
        ];

        foreach ($programas as $prog) {
            TrainingProgram::updateOrCreate(['id' => $prog['id']], $prog);
        }
    }
}
