<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transitions')->insert([
            ['source' => 'start', 'next' => 'type_choice', 'back' => null],
            ['source' => 'type_choice', 'next' => 'anon_choice', 'back' => 'start'],
            ['source' => 'anon_choice', 'next' => 'sector_choice', 'back' => 'type_choice'],
            ['source' => 'sector_choice', 'next' => 'subject_choice', 'back' => 'anon_choice'],
            ['source' => 'subject_choice', 'next' => 'theme_request', 'back' => 'sector_choice'],
            ['source' => 'theme_request', 'next' => null, 'back' => 'subject_choice'],
        ]);
    }
}
