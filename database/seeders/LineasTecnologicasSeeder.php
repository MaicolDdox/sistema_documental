<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LineasTecnologicasSeeder extends Seeder
{
    public function run(): void
    {
        $lineas = [
            'Diseño de Productos, Producción y Transformación, Materiales y Biotecnología',
            "TIC's e Inteligencia Artificial, Usuario, Comercialización y Logística",
            'Sociedad, Cultura y Pedagogía, Economía Popular y Campesina, o Línea SENA se transforma',
        ];

        foreach ($lineas as $nombre) {
            \App\Models\TechnologicalLine::firstOrCreate(
                ['nombre' => $nombre],
                ['descripccion' => null]
            );
        }
    }
}
