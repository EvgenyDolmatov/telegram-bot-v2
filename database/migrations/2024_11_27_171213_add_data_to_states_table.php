<?php

use App\Enums\StateEnum;
use App\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TODO: prepare to prod...
        foreach (State::all() as $state) {
            switch ($state->code) {
                case 'type_choice':
                    $state->update(['code' => StateEnum::POLL_TYPE_CHOICE->value]);
                    break;
                case 'anon_choice':
                    $state->update(['code' => StateEnum::POLL_ANONYMITY_CHOICE->value]);
                    break;
                case 'difficulty_choice':
                    $state->update(['code' => StateEnum::POLL_DIFFICULTY_CHOICE->value]);
                    break;
                case 'sector_choice':
                    $state->update(['code' => StateEnum::POLL_SECTOR_CHOICE->value]);
                    break;
                case 'subject_choice':
                    $state->update(['code' => StateEnum::POLL_SUBJECT_CHOICE->value]);
                    break;
                case 'theme_request':
                    $state->update(['code' => StateEnum::POLL_THEME_WAITING->value]);
                    break;
                case 'ai_response':
                    $state->update(['code' => StateEnum::POLL_AI_RESPONDED_CHOICE->value]);
                    break;
                case 'newsletter_waiting':
                    $state->update([
                        'code' => StateEnum::CHANNEL_POLLS_CHOICE->value,
                        'text' => StateEnum::CHANNEL_POLLS_CHOICE->title()
                    ]);
                    break;
            }

            State::create([
                'code' => StateEnum::CHANNEL_NAME_WAITING->value,
                'text' => StateEnum::CHANNEL_NAME_WAITING->title()
            ]);

            State::create([
                'code' => StateEnum::CHANNEL_POLLS_SENT_SUCCESS->value,
                'text' => StateEnum::CHANNEL_POLLS_SENT_SUCCESS->title()
            ]);

            State::create([
                'code' => StateEnum::POLL_SUPPORT->value,
                'text' => StateEnum::POLL_SUPPORT->title()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            //
        });
    }
};
