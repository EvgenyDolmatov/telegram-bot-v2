<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            StateSeeder::class,
            StateButtonSeeder::class,
            TransitionSeeder::class,
            SectorSeeder::class,
            SubjectSeeder::class,
        ]);
    }
}
