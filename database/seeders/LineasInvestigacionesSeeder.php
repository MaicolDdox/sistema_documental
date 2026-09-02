<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LineasInvestigacionesSeeder extends Seeder
{
    public function run(): void
    {
        $lineas = [
            'Producción Agropecuaria',
            'Desarrollo Agroindustrial de Base Tecnológica',
            'Empresarismo e Inteligencia de Mercados de Base Tecnológica',
            'Gestión Ambiental y Aprovechamiento Sostenible de los Recursos Naturales',
            'TIC Aplicadas al Desarrollo Sostenible',
            'Innovación y Transformación Educativa',
        ];

        foreach ($lineas as $nombre) {
            \App\Models\ResearchLine::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => null]
            );
        }
    }
}
