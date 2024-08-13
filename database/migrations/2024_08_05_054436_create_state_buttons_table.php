<?php

use App\Constants\CallbackConstants;
use App\Constants\StateConstants;
use App\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const string STATE_ID = 'state_id';
    private const string TEXT = 'text';
    private const string CALLBACK = 'callback';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('state_buttons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->references('id')->on('states')->cascadeOnDelete();
            $table->string('text');
            $table->string('callback');
        });

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
                    [
                        self::STATE_ID => $state->id,
                        self::TEXT => 'Любая сложность',
                        self::CALLBACK => CallbackConstants::LEVEL_HARD,
                    ],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_buttons');
    }
};
