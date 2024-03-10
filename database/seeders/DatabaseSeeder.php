<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamamos al seed para la base de datos
        $this->call([
            GrupoSeeder::class
        ]);
        $this->call([
            EmpleadoSeeder::class
        ]);
    }
}
