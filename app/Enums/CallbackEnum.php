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
    case GameTimeLimit15 = 'game_time_limit_15';
    case GameTimeLimit20 = 'game_time_limit_20';
    case GameTimeLimit25 = 'game_time_limit_25';
    case GameTimeLimit30 = 'game_time_limit_30';
    case GameTimeLimit45 = 'game_time_limit_45';
    case GameTimeLimit60 = 'game_time_limit_60';
    case GameTimeLimit180 = 'game_time_limit_180';
    case GameTimeLimit300 = 'game_time_limit_300';
    case GameTimeLimit600 = 'game_time_limit_600';
    case GameEdit = 'game_edit';
    case GameEditTitle = 'game_edit_title';
    case GameEditPolls = 'game_edit_polls';
    case GameEditTimeLimit = 'game_edit_time_limit';
    case GameTitleChange = 'game_title_change';
    case GamePollsChange = 'game_polls_change';
    case GameTimeLimitChange = 'game_time_limit_change';
    case GameAddToCommunity = 'game_add_to_community';
    case GameInvitationLink = 'game_invitation_link';
    case GameStart = 'game_start';
    case GameStatistics = 'game_statistics';



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
            /** Common */
            self::CreateSurveyWithAi => StateEnum::PollTypeChoice,
            self::Support => StateEnum::PollSupport,
            /** Poll */
            self::TypeQuiz,
            self::TypeSurvey => StateEnum::PollThemeChoice,
            self::RepeatFlow => StateEnum::PollAiRespondedChoice,
            self::AfterAiRespondedMenu => StateEnum::PollAfterAiRespondedChoice,
            /** Game */
            self::GameCreate => StateEnum::GameTitleWaiting,
            self::GamePollsSave => StateEnum::GameTimeLimitChoice,
            self::GameTimeLimit15,
            self::GameTimeLimit20,
            self::GameTimeLimit25,
            self::GameTimeLimit30,
            self::GameTimeLimit45,
            self::GameTimeLimit60,
            self::GameTimeLimit180,
            self::GameTimeLimit300,
            self::GameTimeLimit600 => StateEnum::GameCreatedMenuShow,
            self::GameEdit => StateEnum::GameEditMenuShow,
            self::GameEditTitle => StateEnum::GameEditTitleWaiting,
            self::GameEditPolls => StateEnum::GameEditPollsChoice,
            self::GameEditTimeLimit,
            self::GameTitleChange,
            self::GamePollsChange,
            self::GameTimeLimitChange => StateEnum::GameEditTimeLimitChoice,




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

            /** Game button texts */
            self::GamePollsSave => "Отправить выбранные",
            self::GameTimeLimit15 => "15 секунд",
            self::GameTimeLimit20 => "20 секунд",
            self::GameTimeLimit25 => "25 секунд",
            self::GameTimeLimit30 => "30 секунд",
            self::GameTimeLimit45 => "45 секунд",
            self::GameTimeLimit60 => "1 минута",
            self::GameTimeLimit180 => "3 минуты",
            self::GameTimeLimit300 => "5 минут",
            self::GameTimeLimit600 => "10 минут",
            self::GameEdit => "Редактировать",
            self::GameEditTitle => "Редактировать название",
            self::GameEditPolls => "Редактировать вопросы",
            self::GameEditTimeLimit => "Редактировать время",
            self::GamePollsChange,
            self::GameTimeLimitChange => "Сохранить",


            self::GameAddToCommunity => "Добавить в группу",
            self::GameInvitationLink => "Пригласить игроков",
            self::GameStart => "Начать игру",
            self::GameStatistics => "Статистика",

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
