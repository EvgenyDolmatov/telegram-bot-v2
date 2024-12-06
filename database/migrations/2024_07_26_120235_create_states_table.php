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
            [self::CODE => StateEnum::START->value],
            [self::CODE => StateEnum::POLL_TYPE_CHOICE->value],
            [self::CODE => StateEnum::POLL_ANONYMITY_CHOICE->value],
            [self::CODE => StateEnum::POLL_DIFFICULTY_CHOICE->value],
            [self::CODE => StateEnum::POLL_SECTOR_CHOICE->value],
            [self::CODE => StateEnum::POLL_SUBJECT_CHOICE->value],
            [self::CODE => StateEnum::POLL_THEME_WAITING->value],
            [self::CODE => StateEnum::POLL_AI_RESPONDED_CHOICE->value],
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
