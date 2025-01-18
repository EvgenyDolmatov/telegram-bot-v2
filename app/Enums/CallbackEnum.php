<?php

namespace App\Enums;

enum CallbackEnum: string
{
    /** Common */
    case Back = 'back';
    case Support = 'support';
    case CreateSurveyWithAi = 'create_survey_with_ai';

    /** Poll */
    case TypeQuiz = 'type_quiz';
    case TypeSurvey = 'type_survey';
    case RepeatFlow = 'repeat_flow';
    case AfterAiRespondedMenu = 'after_ai_responded_menu';

    /** Game */
    case GameCreate = 'game_create';
    case GamePollsSave = 'game_polls_save';
    case GameTitleSave = 'game_title_save';
    case GameDescriptionSave = 'game_description_save';
    case GameTimeLimitSave = 'game_time_limit_save';
    case GameChannelSave = 'game_channel_save';
    case GameQuizStart = 'game_quiz_start';
    case GameJoinUserToQuiz = 'game_join_user_to_quiz'; // Show in communities

    /** Account */
    case AccountReferralLink = 'account_referral_link';
    case AccountReferredUsers = 'account_referred_users';

    /** Admin */
    case AdminNewsletterCreate = 'admin_newsletter_create';
    case AdminNewsletterChange = 'admin_newsletter_change';
    case AdminNewsletterAccept = 'admin_newsletter_accept';
    case AdminStatisticMenu = 'admin_statistic_menu';
    case AdminStatisticPolls = 'admin_statistic_polls';
    case AdminStatisticPollsPerYear = 'admin_statistic_polls_per_year';
    case AdminStatisticPollsPerQuarter = 'admin_statistic_polls_per_quarter';
    case AdminStatisticPollsPerMonth = 'admin_statistic_polls_per_month';
    case AdminStatisticPollsPerWeek = 'admin_statistic_polls_per_week';
    case AdminStatisticPollsPerDay = 'admin_statistic_polls_per_day';
    case AdminStatisticUsers = 'admin_statistic_users';
    case AdminStatisticUsersPerDay = 'admin_statistic_users_per_day';

    public function toState(): StateEnum
    {
        return match ($this) {
            self::CreateSurveyWithAi => StateEnum::PollTypeChoice,
            self::Support => StateEnum::PollSupport,
            self::TypeQuiz,
            self::TypeSurvey => StateEnum::PollThemeChoice,
            self::RepeatFlow => StateEnum::PollAiRespondedChoice,
            self::AfterAiRespondedMenu => StateEnum::PollAfterAiRespondedChoice,
            /** Game */
            self::GameCreate => StateEnum::GameTitleWaiting,
            self::GameTitleSave => StateEnum::GamePollsChoice,
            self::GamePollsSave => StateEnum::GameTimeLimitWaiting,
            self::GameTimeLimitSave => StateEnum::GameChannelWaiting,

            self::GameDescriptionSave => StateEnum::GameTimeLimitWaiting, // TODO: Remove
            self::GameChannelSave => StateEnum::GameCreatedSuccessShow,
            self::GameQuizStart => StateEnum::GamePlayersWaiting,
            self::GameJoinUserToQuiz => StateEnum::GameQuizProcess,

            self::AccountReferralLink => StateEnum::AccountReferralLinkShow,
            self::AccountReferredUsers => StateEnum::AccountReferredUsersShow,
            self::AdminNewsletterCreate,
            self::AdminNewsletterChange => StateEnum::AdminNewsletterWaiting,
            self::AdminNewsletterAccept => StateEnum::AdminNewsletterSentSuccess,
            self::AdminStatisticMenu => StateEnum::AdminStatisticMenuChoice,
            self::AdminStatisticPolls => StateEnum::AdminStatisticPollsMenuChoice,
            self::AdminStatisticPollsPerYear => StateEnum::AdminStatisticPollsPerYearShow,
            self::AdminStatisticPollsPerQuarter => StateEnum::AdminStatisticPollsPerQuarterShow,
            self::AdminStatisticPollsPerMonth => StateEnum::AdminStatisticPollsPerMonthShow,
            self::AdminStatisticPollsPerWeek => StateEnum::AdminStatisticPollsPerWeekShow,
            self::AdminStatisticPollsPerDay => StateEnum::AdminStatisticPollsPerDayShow,
            self::AdminStatisticUsers => StateEnum::AdminStatisticUsersMenuChoice,
            self::AdminStatisticUsersPerDay => StateEnum::AdminStatisticUsersPerDayShow,
        };
    }

    public function buttonText(): string
    {
        return match ($this) {
            /** Common button texts */
            self::Back => "↩️ Назад",
            self::Support => "Поддержка",
            self::CreateSurveyWithAi => "Создать с Corgish AI",

            /** Poll button texts */
            self::TypeQuiz => "Викторина",
            self::TypeSurvey => "Опрос",
            self::RepeatFlow => "🔄 Создать еще 5 вопросов",
            self::AfterAiRespondedMenu => "🎲 Завершить",
            self::GameCreate => "Создать игру из вопросов",

            self::GamePollsSave => "Сохранить выбранные вопросы",
            self::GameTitleSave => "Сохранить название",
            self::GameDescriptionSave => "Сохранить описание",
            self::GameTimeLimitSave => "Сохранить ограничение по времени",
            self::GameChannelSave => "Сохранить канал",
            self::GameQuizStart => "Отправить в канал",
            self::GameJoinUserToQuiz => "Присоединиться к викторине",
            self::AccountReferralLink => "Моя реферальная ссылка",
            self::AccountReferredUsers => "Приглашенные пользователи",
            self::AdminNewsletterCreate => 'Создать рассылку',
            self::AdminNewsletterChange => '❌ Загрузить другое сообщение',
            self::AdminNewsletterAccept => '✅ Все верно, отправить сообщение всем участникам!',
            self::AdminStatisticMenu => 'Статистика бота',
            self::AdminStatisticPolls => 'Статистика тестов',
            self::AdminStatisticPollsPerYear => 'За год',
            self::AdminStatisticPollsPerQuarter => 'За квартал',
            self::AdminStatisticPollsPerMonth => 'За месяц',
            self::AdminStatisticPollsPerWeek => 'За неделю',
            self::AdminStatisticPollsPerDay => 'За сегодня',
            self::AdminStatisticUsers => 'Статистика пользователей',
            self::AdminStatisticUsersPerDay => 'Новые пользователи сегодня',
        };
    }
}
