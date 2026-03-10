<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CargosEntidadesSeeder extends Seeder
{
    /**
     * Siembra la tabla entity_positions con los cargos institucionales del SENA.
     * Usa firstOrCreate para ser idempotente.
     */
    public function run(): void
    {
        $cargos = [
            // Roles SENNOVA / institucionales
            ['nombre' => 'Articulador Tecnoparque',        'descripccion' => 'Roles institucionales SENNA'],
            ['nombre' => 'Dinamizador Sennova',             'descripccion' => 'Roles institucionales SENNA'],
            ['nombre' => 'Facilitador Tecnoacademia',       'descripccion' => 'Roles institucionales SENNA'],
            ['nombre' => 'Investigador experto',            'descripccion' => 'Roles institucionales SENNA'],
            ['nombre' => 'Líder de grupo de investigación', 'descripccion' => 'Roles institucionales SENNA'],
            ['nombre' => 'Líder de semillero',              'descripccion' => 'Roles institucionales SENNA'],
            // Roles de apoyo
            ['nombre' => 'Auxiliar editorial',                   'descripccion' => 'Roles de apoyo'],
            ['nombre' => 'Personal técnico de laboratorio',      'descripccion' => 'Roles de apoyo'],
            ['nombre' => 'Responsable de propiedad intelectual', 'descripccion' => 'Roles de apoyo'],
            // Roles académicos
            ['nombre' => 'Instructor investigador', 'descripccion' => 'Roles académicos'],
            ['nombre' => 'Aprendiz semillero',      'descripccion' => 'Roles académicos'],
        ];

        foreach ($cargos as $cargo) {
            \App\Models\EntityPosition::firstOrCreate(
                ['nombre' => $cargo['nombre']],
                ['descripccion' => $cargo['descripccion']]
            );
        }
    }
}
