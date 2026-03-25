<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntityPositionsSeeder extends Seeder
{
    public function run(): void
    {
        $posiciones = [
            ['nombre' => 'Titulada',                   'descripccion' => 'Formación titulada del SENA.'],
            ['nombre' => 'Externos',                   'descripccion' => 'Participantes externos a la institución.'],
            ['nombre' => 'Tecno academia',             'descripccion' => 'Participantes de la Tecno academia SENA.'],
            ['nombre' => 'Articulación con la media',  'descripccion' => 'Estudiantes en articulación con la educación media.'],
        ];

        foreach ($posiciones as $pos) {
            DB::table('entity_positions')->updateOrInsert(
                ['nombre' => $pos['nombre']],
                array_merge($pos, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
