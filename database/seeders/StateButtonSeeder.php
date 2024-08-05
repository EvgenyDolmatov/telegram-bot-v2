<?php

namespace Database\Seeders;

use App\Constants\CallbackConstants;
use App\Constants\StateConstants;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateButtonSeeder extends Seeder
{
    private const string STATE_ID = 'state_id';
    private const string TEXT = 'text';
    private const string CALLBACK = 'callback';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (State::all() as $state) {
            if ($state->code === StateConstants::START) {
                DB::table('state_buttons')->insert([
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Создать тест',
                        self::CALLBACK => CallbackConstants::CREATE_SURVEY,
                    ],
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Поддержка',
                        self::CALLBACK => CallbackConstants::SUPPORT,
                    ],
                ]);
            }

            if ($state->code === StateConstants::TYPE_CHOICE) {
                DB::table('state_buttons')->insert([
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Викторина (1 вариант ответа)',
                        self::CALLBACK => CallbackConstants::TYPE_QUIZ,
                    ],
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Опрос (несколько вариантов)',
                        self::CALLBACK => CallbackConstants::TYPE_SURVEY,
                    ],
                ]);
            }

            if ($state->code === StateConstants::ANON_CHOICE) {
                DB::table('state_buttons')->insert([
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Да',
                        self::CALLBACK => CallbackConstants::IS_ANON,
                    ],
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Нет',
                        self::CALLBACK => CallbackConstants::IS_NOT_ANON,
                    ],
                ]);
            }

            if ($state->code === StateConstants::DIFFICULTY_CHOICE) {
                DB::table('state_buttons')->insert([
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Низкая сложность',
                        self::CALLBACK => CallbackConstants::LEVEL_EASY,
                    ],
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Средняя сложность',
                        self::CALLBACK => CallbackConstants::LEVEL_MIDDLE,
                    ],
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Высокая сложность',
                        self::CALLBACK => CallbackConstants::LEVEL_HARD,
                    ],
                ]);
            }
        }
    }
}
