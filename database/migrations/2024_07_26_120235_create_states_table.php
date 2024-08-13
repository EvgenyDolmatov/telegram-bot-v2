<?php

use App\Constants\StateConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const string CODE = 'code';
    private const string TEXT = 'text';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('text')->nullable();
        });

        Schema::create('user_states', function (Blueprint $table) {
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('state_id')->references('id')->on('states')->cascadeOnDelete();
        });

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_states');
        Schema::dropIfExists('states');
    }
};
