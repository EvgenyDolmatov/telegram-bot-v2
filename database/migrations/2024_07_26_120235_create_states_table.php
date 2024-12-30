<?php

use App\Enums\StateEnum;
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
            [self::CODE => StateEnum::Start->value],
            [self::CODE => StateEnum::PollTypeChoice->value],
            [self::CODE => StateEnum::PollAnonymityChoice->value],
            [self::CODE => StateEnum::PollDifficultyChoice->value],
            [self::CODE => StateEnum::PollSectorChoice->value],
            [self::CODE => StateEnum::PollSubjectChoice->value],
            [self::CODE => StateEnum::PollThemeWaiting->value],
            [self::CODE => StateEnum::PollAiRespondedChoice->value],
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
