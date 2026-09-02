<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Orden de dependencias:
     * 1. Departments → sin FK
     * 2. Cities → depende de Department
     * 3. TrainingCenters → depende de Department + City
     * 4. SuperAdmin → depende de TrainingCenter + roles
     *
     * No siembra usuarios de prueba con datos ficticios (nombre, email,
     * contraseña compartida) — eso quedaría publicado en el repo público.
     * Para poblar usuarios de prueba en un entorno local, créalos a mano
     * o con un seeder/script propio no versionado.
     */
    public function run(): void
    {
        $this->call([

            DepartmentSeeder::class,
            CitySeeder::class,
            TrainingCenterSeeder::class,
            RolesAndPermissionsSeeder::class, // ← debe ir ANTES de SuperAdminSeeder
            SuperAdminSeeder::class,

            // Catálogos
            LineasInvestigacionesSeeder::class,
            LineasTecnologicasSeeder::class,
            AreasTematicasSeeder::class,
            ModalidadesProyectosSeeder::class,
            TiposInvestigacionesSeeder::class,
            CargosEntidadesSeeder::class,
            LinkageTypesSeeder::class,
            TrainingProgramsSeeder::class,
            MincienciasSeeder::class,
        ]);
    }
}
