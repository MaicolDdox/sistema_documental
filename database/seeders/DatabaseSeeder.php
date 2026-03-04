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
     * 4. Users → depende de TrainingCenter
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            CitySeeder::class,
            TrainingCenterSeeder::class,
            UserSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
    }
}
