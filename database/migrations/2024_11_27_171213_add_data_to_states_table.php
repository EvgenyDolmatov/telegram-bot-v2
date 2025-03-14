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
                    $state->update(['code' => StateEnum::POLL_SUPPORT->value]);
                    break;
            }
        }

        /** Game */
        State::create(['code' => StateEnum::GAME_POLLS_CHOICE->value]);
        State::create(['code' => StateEnum::GAME_TITLE_WAITING->value]);
        State::create(['code' => StateEnum::GAME_DESCRIPTION_WAITING->value]);
        State::create(['code' => StateEnum::GAME_TIME_LIMIT_WAITING->value]);
        State::create(['code' => StateEnum::GAME_CHANNEL_WAITING->value]);
        State::create(['code' => StateEnum::GAME_CREATED_SUCCESS_SHOW->value]);
        State::create(['code' => StateEnum::GAME_SENT_TO_CHANNEL_SUCCESS->value]);

        /** Admin */
        State::create(['code' => StateEnum::ADMIN->value]);
        State::create(['code' => StateEnum::ADMIN_NEWSLETTER_WAITING->value]);
        State::create(['code' => StateEnum::ADMIN_NEWSLETTER_CONFIRMATION->value]);
        State::create(['code' => StateEnum::ADMIN_NEWSLETTER_SENT_SUCCESS->value]);
        State::create(['code' => StateEnum::ADMIN_STATISTIC_MENU_CHOICE->value]);
        State::create(['code' => StateEnum::ADMIN_STATISTIC_POLLS_MENU_CHOICE->value]);
        State::create(['code' => StateEnum::ADMIN_STATISTIC_POLLS_PER_YEAR_SHOW->value]);
        State::create(['code' => StateEnum::ADMIN_STATISTIC_POLLS_PER_QUARTER_SHOW->value]);
        State::create(['code' => StateEnum::ADMIN_STATISTIC_POLLS_PER_MONTH_SHOW->value]);
        State::create(['code' => StateEnum::ADMIN_STATISTIC_POLLS_PER_WEEK_SHOW->value]);
        State::create(['code' => StateEnum::ADMIN_STATISTIC_POLLS_PER_DAY_SHOW->value]);
        State::create(['code' => StateEnum::ADMIN_STATISTIC_USERS_MENU_CHOICE->value]);
        State::create(['code' => StateEnum::ADMIN_STATISTIC_USERS_PER_DAY_SHOW->value]);

        /** Account */
        State::create(['code' => StateEnum::ACCOUNT->value]);
        State::create(['code' => StateEnum::ACCOUNT_REFERRAL_LINK_SHOW->value]);
        State::create(['code' => StateEnum::ACCOUNT_REFERRED_USERS_SHOW->value]);
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
