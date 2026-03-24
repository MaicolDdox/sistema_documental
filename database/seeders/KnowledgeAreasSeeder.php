<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KnowledgeAreasSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            'Ciencias Naturales' => ['Matemáticas', 'Ciencias de la computación', 'Ciencias físicas', 'Ciencias químicas', 'Ciencias de la Tierra y medioambientales'],
            'Ingeniería y Tecnología' => ['Ingeniería civil', 'Ingeniería eléctrica, electrónica', 'Ingeniería mecánica', 'Ingeniería química', 'Ingeniería de materiales'],
            'Ciencias Médicas y de Salud' => ['Medicina básica', 'Medicina clínica', 'Ciencias de la salud', 'Biotecnología en salud'],
            'Ciencias Agrícolas' => ['Agricultura, silvicultura, y pesca', 'Ciencias animales y lechería', 'Veterinaria', 'Biotecnología agrícola'],
            'Ciencias Sociales' => ['Psicología', 'Economía y negocios', 'Ciencias de la educación', 'Sociología', 'Derecho'],
            'Humanidades' => ['Historia y arqueología', 'Idiomas y literatura', 'Filosofía, ética y religión', 'Arte'],
        ];

        foreach ($areas as $grandArea => $subAreas) {
            $grandId = DB::table('knowledge_grand_areas')->insertGetId([
                'nombre' => $grandArea,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($subAreas as $area) {
                DB::table('knowledge_areas')->insert([
                    'knowledge_grand_area_id' => $grandId,
                    'nombre' => $area,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
