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

        foreach ($areas as $nombre) {
            \App\Models\ThematicArea::firstOrCreate(
                ['nombre' => $nombre],
                ['descripccion' => null]
            );
        }
    }
}
