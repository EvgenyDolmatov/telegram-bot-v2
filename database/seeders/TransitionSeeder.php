<?php

namespace Database\Seeders;

use App\Constants\StateConstants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransitionSeeder extends Seeder
{
    private const string SOURCE = 'source';
    private const string NEXT = 'next';
    private const string BACK = 'back';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transitions')->insert([
            [
                self::SOURCE => StateConstants::START,
                self::NEXT => StateConstants::TYPE_CHOICE,
                self::BACK => null
            ],
            [
                self::SOURCE => StateConstants::TYPE_CHOICE,
                self::NEXT => StateConstants::ANON_CHOICE,
                self::BACK => StateConstants::START
            ],
            [
                self::SOURCE => StateConstants::ANON_CHOICE,
                self::NEXT => StateConstants::DIFFICULTY_CHOICE,
                self::BACK => StateConstants::TYPE_CHOICE
            ],
            [
                self::SOURCE => StateConstants::DIFFICULTY_CHOICE,
                self::NEXT => StateConstants::SECTOR_CHOICE,
                self::BACK => StateConstants::ANON_CHOICE
            ],
            [
                self::SOURCE => StateConstants::SECTOR_CHOICE,
                self::NEXT => StateConstants::SUBJECT_CHOICE,
                self::BACK => StateConstants::DIFFICULTY_CHOICE
            ],
            [
                self::SOURCE => StateConstants::SUBJECT_CHOICE,
                self::NEXT => StateConstants::THEME_REQUEST,
                self::BACK => StateConstants::SECTOR_CHOICE
            ],
            [
                self::SOURCE => StateConstants::THEME_REQUEST,
                self::NEXT => StateConstants::AI_RESPONSE,
                self::BACK => StateConstants::SUBJECT_CHOICE
            ],
            [
                self::SOURCE => StateConstants::AI_RESPONSE,
                self::NEXT => null,
                self::BACK => StateConstants::THEME_REQUEST
            ],
        ]);
    }
}
