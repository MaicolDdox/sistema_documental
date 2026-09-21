<?php

namespace Database\Seeders;

use App\Models\TrainingCenter;
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

        foreach (TrainingCenter::all() as $centro) {
            // Idempotente por centro: si ya existe una tipología con este
            // código en este centro (de una corrida anterior del seeder), se
            // salta por completo (típologías + subcategorías) para no duplicar.
            if (DB::table('minciencias_typologies')->where('training_center_id', $centro->id)->exists()) {
                continue;
            }

            foreach ($typologies as $typ) {
                $typId = DB::table('minciencias_typologies')->insertGetId([
                    'training_center_id' => $centro->id,
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
                        'training_center_id' => $centro->id,
                        'minciencias_typology_id' => $typId,
                        'nombre' => $sub,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
