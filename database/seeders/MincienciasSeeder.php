<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MincienciasSeeder extends Seeder
{
    public function run(): void
    {
        $typologies = [
            ['nombre' => 'Generación de Nuevo Conocimiento', 'codigo' => 'GNC'],
            ['nombre' => 'Apropiación Social del Conocimiento', 'codigo' => 'ASC'],
            ['nombre' => 'Desarrollo Tecnológico e Innovación', 'codigo' => 'DTI'],
            ['nombre' => 'Formación de Recursos Humanos', 'codigo' => 'FRH'],
        ];

        foreach ($typologies as $typ) {
            $typId = DB::table('minciencias_typologies')->insertGetId([
                'nombre' => $typ['nombre'],
                'codigo' => $typ['codigo'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $subcategories = [];
            if ($typ['codigo'] === 'GNC') {
                $subcategories = ['Artículos de investigación', 'Libros resultado de investigación', 'Capítulos de libro'];
            } elseif ($typ['codigo'] === 'ASC') {
                $subcategories = ['Estrategias de comunicación', 'Eventos científicos', 'Circulación de conocimiento'];
            } elseif ($typ['codigo'] === 'DTI') {
                $subcategories = ['Software', 'Plantas piloto', 'Prototipos', 'Productos empresariales industriales'];
            } elseif ($typ['codigo'] === 'FRH') {
                $subcategories = ['Tesis de doctorado', 'Trabajos de maestría', 'Trabajos de pregrado', 'Cursos de corta duración'];
            }

            foreach ($subcategories as $sub) {
                DB::table('minciencias_subcategories')->insert([
                    'minciencias_typology_id' => $typId,
                    'nombre' => $sub,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
