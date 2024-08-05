<?php

namespace Database\Seeders;

use App\Constants\StateConstants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    private const string CODE = 'code';
    private const string TEXT = 'text';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('states')->insert([
            [
                self::CODE => StateConstants::START,
                self::TEXT => 'Привет! Выбери вариант:'
            ],
            [
                self::CODE => StateConstants::TYPE_CHOICE,
                self::TEXT => 'Выберите тип опроса:'
            ],
            [
                self::CODE => StateConstants::ANON_CHOICE,
                self::TEXT => 'Опрос будет анонимный?'
            ],
            [
                self::CODE => StateConstants::DIFFICULTY_CHOICE,
                self::TEXT => 'Выберите сложность вопросов:'
            ],
            [
                self::CODE => StateConstants::SECTOR_CHOICE,
                self::TEXT => 'Выберите направление:'
            ],
            [
                self::CODE => StateConstants::SUBJECT_CHOICE,
                self::TEXT => 'Выберите предмет:'
            ],
            [
                self::CODE => StateConstants::THEME_REQUEST,
                self::TEXT => 'Введите свой вопрос:'
            ],
            [
                self::CODE => StateConstants::AI_RESPONSE,
                self::TEXT => 'Подождите. Ваш запрос обрабатывается...'
            ],
        ]);
    }
}
