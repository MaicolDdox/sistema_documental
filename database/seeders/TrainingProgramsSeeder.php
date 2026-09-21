<?php

namespace Database\Seeders;

use App\Enums\EstadoEnum;
use App\Enums\ModalidadEnum;
use App\Models\TrainingCenter;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramType;
use Illuminate\Database\Seeder;

class TrainingProgramsSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Tecnólogo' => 'Programas de nivel tecnológico.',
            'Técnico' => 'Programas de nivel técnico.',
            'Especialización Tecnológica' => 'Especialización nivel tecnológico.',
        ];

        $programas = [
            [
                'tipo' => 'Tecnólogo',
                'nombre' => 'Análisis y Desarrollo de Software',
                'descripcion' => 'ADSO',
                'modalidad' => ModalidadEnum::Presencial->value,
                'estado' => EstadoEnum::Activo->value,
            ],
            [
                'tipo' => 'Tecnólogo',
                'nombre' => 'Gestión Administrativa',
                'descripcion' => 'Gestión Administrativa',
                'modalidad' => ModalidadEnum::Virtual->value,
                'estado' => EstadoEnum::Activo->value,
            ],
        ];

        foreach (TrainingCenter::all() as $centro) {
            $tipoIds = [];
            foreach ($tipos as $nombre => $descripcion) {
                $tipoIds[$nombre] = TrainingProgramType::updateOrCreate(
                    ['training_center_id' => $centro->id, 'nombre' => $nombre],
                    ['descripcion' => $descripcion]
                )->id;
            }

            foreach ($programas as $prog) {
                TrainingProgram::updateOrCreate(
                    ['training_center_id' => $centro->id, 'nombre' => $prog['nombre']],
                    [
                        'training_program_type_id' => $tipoIds[$prog['tipo']],
                        'descripcion' => $prog['descripcion'],
                        'modalidad' => $prog['modalidad'],
                        'estado' => $prog['estado'],
                    ]
                );
            }
        }
    }
}
