<?php

namespace App\Enums;

enum CallbackEnum: string
{
    /** Common */
    case Back = 'back';

    /** Poll */
    case CreateSurvey = 'create_survey';
    case Support = 'support';
    case TypeQuiz = 'type_quiz';
    case TypeSurvey = 'type_survey';
    case IsAnon = 'is_anon';
    case IsNotAnon = 'is_not_anon';
    case LevelEasy = 'level_easy';
    case LevelMiddle = 'level_middle';
    case LevelHard = 'level_hard';
    case LevelAny = 'level_any';
    case RepeatFlow = 'repeat_flow';

    /** Game */
    case GAME_CREATE = 'game_create';
    case GAME_POLLS_SAVE = 'game_polls_save';
    case GAME_TITLE_SAVE = 'game_title_save';
    case GAME_DESCRIPTION_SAVE = 'game_description_save';
    case GAME_TIME_LIMIT_SAVE = 'game_time_limit_save';
    case GAME_CHANNEL_SAVE = 'game_channel_save';
    case GameQuizStart = 'game_quiz_start';
    case GameJoinUserToQuiz = 'game_join_user_to_quiz'; // Show in communities

    /** Account */
    case ACCOUNT_REFERRAL_LINK = 'account_referral_link';
    case ACCOUNT_REFERRED_USERS = 'account_referred_users';

    /** Admin */
    case ADMIN_NEWSLETTER_CREATE = 'admin_newsletter_create';
    case ADMIN_NEWSLETTER_CHANGE = 'admin_newsletter_change';
    case ADMIN_NEWSLETTER_ACCEPT = 'admin_newsletter_accept';
    case ADMIN_STATISTIC_MENU = 'admin_statistic_menu';
    case ADMIN_STATISTIC_POLLS = 'admin_statistic_polls';
    case ADMIN_STATISTIC_POLLS_PER_YEAR = 'admin_statistic_polls_per_year';
    case ADMIN_STATISTIC_POLLS_PER_QUARTER = 'admin_statistic_polls_per_quarter';
    case ADMIN_STATISTIC_POLLS_PER_MONTH = 'admin_statistic_polls_per_month';
    case ADMIN_STATISTIC_POLLS_PER_WEEK = 'admin_statistic_polls_per_week';
    case ADMIN_STATISTIC_POLLS_PER_DAY = 'admin_statistic_polls_per_day';
    case ADMIN_STATISTIC_USERS = 'admin_statistic_users';
    case ADMIN_STATISTIC_USERS_PER_DAY = 'admin_statistic_users_per_day';

    public function toState(): StateEnum
    {
        return match ($this) {
            self::CreateSurvey => StateEnum::POLL_TYPE_CHOICE,
            self::Support => StateEnum::POLL_SUPPORT,
            self::TypeQuiz,
            self::TypeSurvey => StateEnum::POLL_ANONYMITY_CHOICE,
            self::IsAnon,
            self::IsNotAnon => StateEnum::POLL_DIFFICULTY_CHOICE,
            self::LevelHard,
            self::LevelMiddle,
            self::LevelEasy,
            self::LevelAny => StateEnum::POLL_SECTOR_CHOICE,
            self::RepeatFlow => StateEnum::POLL_AI_RESPONDED_CHOICE,
            /** Game */
            self::GAME_CREATE => StateEnum::GAME_POLLS_CHOICE,
            self::GAME_POLLS_SAVE => StateEnum::GAME_TITLE_WAITING,
            self::GAME_TITLE_SAVE => StateEnum::GAME_DESCRIPTION_WAITING,
            self::GAME_DESCRIPTION_SAVE => StateEnum::GAME_TIME_LIMIT_WAITING,
            self::GAME_TIME_LIMIT_SAVE => StateEnum::GAME_CHANNEL_WAITING,
            self::GAME_CHANNEL_SAVE => StateEnum::GAME_CREATED_SUCCESS_SHOW,
            self::GameQuizStart => StateEnum::GamePlayersWaiting,
            self::GameJoinUserToQuiz => StateEnum::GameQuizProcess,

            self::ACCOUNT_REFERRAL_LINK => StateEnum::ACCOUNT_REFERRAL_LINK_SHOW,
            self::ACCOUNT_REFERRED_USERS => StateEnum::ACCOUNT_REFERRED_USERS_SHOW,
            self::ADMIN_NEWSLETTER_CREATE,
            self::ADMIN_NEWSLETTER_CHANGE => StateEnum::ADMIN_NEWSLETTER_WAITING,
            self::ADMIN_NEWSLETTER_ACCEPT => StateEnum::ADMIN_NEWSLETTER_SENT_SUCCESS,
            self::ADMIN_STATISTIC_MENU => StateEnum::ADMIN_STATISTIC_MENU_CHOICE,
            self::ADMIN_STATISTIC_POLLS => StateEnum::ADMIN_STATISTIC_POLLS_MENU_CHOICE,
            self::ADMIN_STATISTIC_POLLS_PER_YEAR => StateEnum::ADMIN_STATISTIC_POLLS_PER_YEAR_SHOW,
            self::ADMIN_STATISTIC_POLLS_PER_QUARTER => StateEnum::ADMIN_STATISTIC_POLLS_PER_QUARTER_SHOW,
            self::ADMIN_STATISTIC_POLLS_PER_MONTH => StateEnum::ADMIN_STATISTIC_POLLS_PER_MONTH_SHOW,
            self::ADMIN_STATISTIC_POLLS_PER_WEEK => StateEnum::ADMIN_STATISTIC_POLLS_PER_WEEK_SHOW,
            self::ADMIN_STATISTIC_POLLS_PER_DAY => StateEnum::ADMIN_STATISTIC_POLLS_PER_DAY_SHOW,
            self::ADMIN_STATISTIC_USERS => StateEnum::ADMIN_STATISTIC_USERS_MENU_CHOICE,
            self::ADMIN_STATISTIC_USERS_PER_DAY => StateEnum::ADMIN_STATISTIC_USERS_PER_DAY_SHOW,
        };
    }

    public function buttonText(): string
    {
        return match ($this) {
            self::Back => "↩️ Назад",
            self::CreateSurvey => "Создать тест",
            self::Support => "Поддержка",
            self::TypeQuiz => "Викторина (1 вариант ответа)",
            self::TypeSurvey => "Опрос (несколько вариантов)",
            self::IsAnon => "Да",
            self::IsNotAnon => "Нет",
            self::LevelHard => "Высокая сложность",
            self::LevelMiddle => "Средняя сложность",
            self::LevelEasy => "Низкая сложность",
            self::LevelAny => "Любая сложность",
            self::RepeatFlow => "🔄 Создать еще 5 вопросов",
            self::GAME_CREATE => "🎲 Создать игру для канала",
            self::GAME_POLLS_SAVE => "Сохранить выбранные вопросы",
            self::GAME_TITLE_SAVE => "Сохранить название",
            self::GAME_DESCRIPTION_SAVE => "Сохранить описание",
            self::GAME_TIME_LIMIT_SAVE => "Сохранить ограничение по времени",
            self::GAME_CHANNEL_SAVE => "Сохранить канал",
            self::GameQuizStart => "Отправить в канал",
            self::GameJoinUserToQuiz => "Присоединиться к викторине",
            self::ACCOUNT_REFERRAL_LINK => "Моя реферальная ссылка",
            self::ACCOUNT_REFERRED_USERS => "Приглашенные пользователи",
            self::ADMIN_NEWSLETTER_CREATE => 'Создать рассылку',
            self::ADMIN_NEWSLETTER_CHANGE => '❌ Загрузить другое сообщение',
            self::ADMIN_NEWSLETTER_ACCEPT => '✅ Все верно, отправить сообщение всем участникам!',
            self::ADMIN_STATISTIC_MENU => 'Статистика бота',
            self::ADMIN_STATISTIC_POLLS => 'Статистика тестов',
            self::ADMIN_STATISTIC_POLLS_PER_YEAR => 'За год',
            self::ADMIN_STATISTIC_POLLS_PER_QUARTER => 'За квартал',
            self::ADMIN_STATISTIC_POLLS_PER_MONTH => 'За месяц',
            self::ADMIN_STATISTIC_POLLS_PER_WEEK => 'За неделю',
            self::ADMIN_STATISTIC_POLLS_PER_DAY => 'За сегодня',
            self::ADMIN_STATISTIC_USERS => 'Статистика пользователей',
            self::ADMIN_STATISTIC_USERS_PER_DAY => 'Новые пользователи сегодня',
        };
    }
}
