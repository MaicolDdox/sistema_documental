<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LineasTecnologicasSeeder extends Seeder
{
    public function run(): void
    {
        $lineas = [
            'Línea de Economía Popular y Campesina',
            'Línea de Materiales y Biotecnología',
            'Línea de Usuario, Comercialización y Logística',
            'Línea de Producción y Transformación',
            'Línea de TICs e Inteligencia Artificial',
            'Línea de Sociedad, Cultura y Pedagogía',
        ];

        foreach ($lineas as $nombre) {
            \App\Models\TechnologicalLine::firstOrCreate(
                ['nombre' => $nombre],
                ['descripccion' => null]
            );
        }
    }
}
