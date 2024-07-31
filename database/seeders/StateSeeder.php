<?php

namespace Database\Seeders;

use App\Constants\StateConstants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    private const string CODE = 'code';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('states')->insert([
            [self::CODE => StateConstants::START],
            [self::CODE => StateConstants::TYPE_CHOICE],
            [self::CODE => StateConstants::ANON_CHOICE],
            [self::CODE => StateConstants::DIFFICULTY_CHOICE],
            [self::CODE => StateConstants::SECTOR_CHOICE],
            [self::CODE => StateConstants::SUBJECT_CHOICE],
            [self::CODE => StateConstants::THEME_REQUEST],
        ]);
    }
}
