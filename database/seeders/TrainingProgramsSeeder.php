<?php

namespace Database\Seeders;

use App\Models\TrainingProgramType;
use App\Models\TrainingProgram;
use App\Enums\EstadoEnum;
use App\Enums\ModalidadEnum;
use Illuminate\Database\Seeder;

class TrainingProgramsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Tipos de Programa (si no existen)
        $tipos = [
            ['id' => 1, 'nombre' => 'Tecnólogo', 'descripcion' => 'Programas de nivel tecnológico.'],
            ['id' => 2, 'nombre' => 'Técnico', 'descripcion' => 'Programas de nivel técnico.'],
            ['id' => 3, 'nombre' => 'Especialización Tecnológica', 'descripcion' => 'Especialización nivel tecnológico.'],
        ];

        foreach ($tipos as $tipo) {
            TrainingProgramType::updateOrCreate(['id' => $tipo['id']], $tipo);
        }

        // 2. Crear Programas de Formación
        $programas = [
            [
                'id' => 1,
                'training_program_type_id' => 1,
                'nombre' => 'Análisis y Desarrollo de Software',
                'descripcion' => 'ADSO',
                'modalidad' => ModalidadEnum::Presencial->value,
                'estado' => EstadoEnum::Activo->value,
            ],
            [
                'id' => 2,
                'training_program_type_id' => 1,
                'nombre' => 'Gestión Administrativa',
                'descripcion' => 'Gestión Administrativa',
                'modalidad' => ModalidadEnum::Virtual->value,
                'estado' => EstadoEnum::Activo->value,
            ]
        ];

        foreach ($programas as $prog) {
            TrainingProgram::updateOrCreate(['id' => $prog['id']], $prog);
        }
    }
}
