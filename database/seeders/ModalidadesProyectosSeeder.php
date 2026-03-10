<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ModalidadesProyectosSeeder extends Seeder
{
    public function run(): void
    {
        $modalidades = [
            [
                'nombre'       => 'Propuesta',
                'descripccion' => 'El proyecto se encuentra en fase de propuesta, aún no ha iniciado.',
            ],
            [
                'nombre'       => 'En curso',
                'descripccion' => 'El proyecto está actualmente en ejecución.',
            ],
            [
                'nombre'       => 'Finalizado',
                'descripccion' => 'El proyecto ha concluido su ejecución.',
            ],
        ];

        foreach ($modalidades as $modalidad) {
            \App\Models\ProjectModality::firstOrCreate(
                ['nombre' => $modalidad['nombre']],
                ['descripccion' => $modalidad['descripccion']]
            );
        }
    }
}
