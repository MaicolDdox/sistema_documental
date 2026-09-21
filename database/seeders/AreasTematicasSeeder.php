<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AreasTematicasSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            'Agrícola',
            'Agroindustrial',
            'Desarrollo de Software',
            'Pecuaria',
            'Administrativo',
            'Ambiental',
            'Pedagogía',
        ];

        foreach (\App\Models\TrainingCenter::all() as $centro) {
            foreach ($areas as $nombre) {
                \App\Models\ThematicArea::firstOrCreate(
                    ['training_center_id' => $centro->id, 'nombre' => $nombre],
                    ['descripcion' => null]
                );
            }
        }
    }
}
