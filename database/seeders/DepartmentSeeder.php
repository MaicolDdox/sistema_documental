<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['nombre' => 'Amazonas'],
            ['nombre' => 'Antioquia'],
            ['nombre' => 'Arauca'],
            ['nombre' => 'Archipiélago de San Andrés, Providencia y Santa Catalina'],
            ['nombre' => 'Atlántico'],
            ['nombre' => 'Bogotá D.C.'],
            ['nombre' => 'Bolívar'],
            ['nombre' => 'Boyacá'],
            ['nombre' => 'Caldas'],
            ['nombre' => 'Caquetá'],
            ['nombre' => 'Casanare'],
            ['nombre' => 'Cauca'],
            ['nombre' => 'Cesar'],
            ['nombre' => 'Chocó'],
            ['nombre' => 'Córdoba'],
            ['nombre' => 'Cundinamarca'],
            ['nombre' => 'Guainía'],
            ['nombre' => 'Guaviare'],
            ['nombre' => 'Huila'],
            ['nombre' => 'La Guajira'],
            ['nombre' => 'Magdalena'],
            ['nombre' => 'Meta'],
            ['nombre' => 'Nariño'],
            ['nombre' => 'Norte de Santander'],
            ['nombre' => 'Putumayo'],
            ['nombre' => 'Quindío'],
            ['nombre' => 'Risaralda'],
            ['nombre' => 'Santander'],
            ['nombre' => 'Sucre'],
            ['nombre' => 'Tolima'],
            ['nombre' => 'Valle del Cauca'],
            ['nombre' => 'Vaupés'],
            ['nombre' => 'Vichada'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['nombre' => $dept['nombre']]);
        }

        $this->command->info('✅ ' . count($departments) . ' departamentos insertados.');
    }
}
