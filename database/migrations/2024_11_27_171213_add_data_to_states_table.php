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
        /** Poll */
        foreach (State::all() as $state) {
            switch ($state->code) {
                case 'type_choice':
                    $state->update(['code' => StateEnum::PollTypeChoice->value]);
                    break;
                case 'anon_choice':
                    $state->update(['code' => StateEnum::PollAnonymityChoice->value]);
                    break;
                case 'difficulty_choice':
                    $state->update(['code' => StateEnum::PollDifficultyChoice->value]);
                    break;
                case 'sector_choice':
                    $state->update(['code' => StateEnum::PollSectorChoice->value]);
                    break;
                case 'subject_choice':
                    $state->update(['code' => StateEnum::PollSubjectChoice->value]);
                    break;
                case 'theme_request':
                    $state->update(['code' => StateEnum::PollThemeWaiting->value]);
                    break;
                case 'ai_response':
                    $state->update(['code' => StateEnum::PollAiRespondedChoice->value]);
                    break;
                case 'newsletter_waiting':
                    $state->update(['code' => StateEnum::PollSupport->value]);
                    break;
            }
        }

        /** Game */
        State::create(['code' => StateEnum::GamePollsChoice->value]);
        State::create(['code' => StateEnum::GameTitleWaiting->value]);
        State::create(['code' => StateEnum::GameDescriptionWaiting->value]);
        State::create(['code' => StateEnum::GameTimeLimitChoice->value]);
        State::create(['code' => StateEnum::GameChannelWaiting->value]);
        State::create(['code' => StateEnum::GameCreatedSuccessShow->value]);
        State::create(['code' => StateEnum::GamePlayersWaiting->value]);
        State::create(['code' => StateEnum::GameQuizProcess->value]);

        /** Admin */
        State::create(['code' => StateEnum::Admin->value]);
        State::create(['code' => StateEnum::AdminNewsletterWaiting->value]);
        State::create(['code' => StateEnum::AdminNewsletterConfirmation->value]);
        State::create(['code' => StateEnum::AdminNewsletterSentSuccess->value]);
        State::create(['code' => StateEnum::AdminStatisticMenuChoice->value]);
        State::create(['code' => StateEnum::AdminStatisticPollsMenuChoice->value]);
        State::create(['code' => StateEnum::AdminStatisticPollsPerYearShow->value]);
        State::create(['code' => StateEnum::AdminStatisticPollsPerQuarterShow->value]);
        State::create(['code' => StateEnum::AdminStatisticPollsPerMonthShow->value]);
        State::create(['code' => StateEnum::AdminStatisticPollsPerWeekShow->value]);
        State::create(['code' => StateEnum::AdminStatisticPollsPerDayShow->value]);
        State::create(['code' => StateEnum::AdminStatisticUsersMenuChoice->value]);
        State::create(['code' => StateEnum::AdminStatisticUsersPerDayShow->value]);

        /** Account */
        State::create(['code' => StateEnum::Account->value]);
        State::create(['code' => StateEnum::AccountReferralLinkShow->value]);
        State::create(['code' => StateEnum::AccountReferredUsersShow->value]);
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
