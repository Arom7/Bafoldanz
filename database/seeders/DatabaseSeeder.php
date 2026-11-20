<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Solo catálogo + directoras (decisión acordada):
 * los alumnos de ejemplo van con DemoStudentsSeeder, solo en dev.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            DirectorSeeder::class,
        ]);
    }
}
