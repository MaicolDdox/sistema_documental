<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AreasTematicasSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            'Ambiental',
            'Agrícola',
            'Agroindustrial',
            'Pecuaria',
            'Pedagógico',
            'TIC',
            'Emprendimiento',
        ];

        foreach ($areas as $nombre) {
            \App\Models\ThematicArea::firstOrCreate(
                ['nombre' => $nombre],
                ['descripccion' => null]
            );
        }
    }
}
