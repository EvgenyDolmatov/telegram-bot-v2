<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('states')->insert([
            ['code' => 'start'],
            ['code' => 'type_choice'],
            ['code' => 'anon_choice'],
            ['code' => 'sector_choice'],
            ['code' => 'subject_choice'],
            ['code' => 'theme_request'],
        ]);
    }
}
