<?php

namespace App\Enums;

use App\Dto\ButtonDto;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Senders\Account\ReferralLinkShowSender;
use App\Senders\Account\ReferredUsersShowSender;
use App\Senders\Admin\NewsletterConfirmationSender;
use App\Senders\Admin\NewsletterSentSuccessSender;
use App\Senders\Admin\NewsletterWaitingSender;
use App\Senders\Admin\StatisticMenuChoiceSender;
use App\Senders\Admin\StatisticPollsMenuChoiceSender;
use App\Senders\Admin\StatisticPollsPerDayShowSender;
use App\Senders\Admin\StatisticPollsPerMonthShowSender;
use App\Senders\Admin\StatisticPollsPerQuarterShowSender;
use App\Senders\Admin\StatisticPollsPerWeekShowSender;
use App\Senders\Admin\StatisticPollsPerYearShowSender;
use App\Senders\Admin\StatisticUsersMenuChoiceSender;
use App\Senders\Admin\StatisticUsersPerDayShowSender;
use App\Senders\Commands\AccountSender;
use App\Senders\Commands\AdminSender;
use App\Senders\Commands\HelpSender;
use App\Senders\Commands\StartSender;
use App\Senders\Game\GameChannelWaitingSender;
use App\Senders\Game\GameCreatedSuccessShowSender;
use App\Senders\Game\GameDescriptionWaitingSender;
use App\Senders\Game\GamePlayersWaitingSender;
use App\Senders\Game\GamePollsChoiceSender;
use App\Senders\Game\GameTimeLimitWaitingSender;
use App\Senders\Game\GameTitleWaitingSender;
use App\Senders\Poll\AiRespondedChoiceSender;
use App\Senders\Poll\AnonymityChoiceSender;
use App\Senders\Poll\DifficultyChoiceSender;
use App\Senders\Poll\SectorChoiceSender;
use App\Senders\Poll\SubjectChoiceSender;
use App\Senders\Poll\SupportSender;
use App\Senders\Poll\ThemeWaitingSender;
use App\Senders\Poll\TypeChoiceSender;
use App\Senders\SenderInterface;
use App\Services\TelegramService;
use App\States\Account\AccountState;
use App\States\Account\ReferralLinkShowState;
use App\States\Account\ReferredUsersShowState;
use App\States\Admin\AdminState;
use App\States\Admin\NewsletterConfirmationState;
use App\States\Admin\NewsletterSentSuccessState;
use App\States\Admin\NewsletterWaitingState;
use App\States\Admin\StatisticMenuChoiceState;
use App\States\Admin\StatisticPollsMenuChoiceState;
use App\States\Admin\StatisticPollsPerDayShowState;
use App\States\Admin\StatisticPollsPerMonthShowState;
use App\States\Admin\StatisticPollsPerQuarterShowState;
use App\States\Admin\StatisticPollsPerWeekShowState;
use App\States\Admin\StatisticPollsPerYearShowState;
use App\States\Admin\StatisticUsersMenuChoiceState;
use App\States\Admin\StatisticUsersPerDayShowState;
use App\States\Game\GameChannelWaitingState;
use App\States\Game\GameCreatedSuccessState;
use App\States\Game\GameDescriptionWaitingState;
use App\States\Game\GamePlayersWaitingState;
use App\States\Game\GamePollsChoiceState;
use App\States\Game\GameTimeLimitWaitingState;
use App\States\Game\GameTitleWaitingState;
use App\States\Help\HelpState;
use App\States\Poll\AiRespondedChoiceState;
use App\States\Poll\AnonymityChoiceState;
use App\States\Poll\DifficultyChoiceState;
use App\States\Poll\SectorChoiceState;
use App\States\Poll\SubjectChoiceState;
use App\States\Poll\SupportState;
use App\States\Poll\ThemeWaitingState;
use App\States\Poll\TypeChoiceState;
use App\States\StartState;
use App\States\UserState;

enum StateEnum: string
{
    /** Poll */
    case Start = 'start';
    case PollSupport = 'poll_support';
    case PollTypeChoice = 'poll_type_choice';
    case PollAnonymityChoice = 'poll_anonymity_choice';
    case PollDifficultyChoice = 'poll_difficulty_choice';
    case PollSectorChoice = 'poll_sector_choice';
    case PollSubjectChoice = 'poll_subject_choice';
    case PollThemeWaiting = 'poll_theme_waiting';
    case PollAiRespondedChoice = 'poll_ai_responded_choice';

    /** Game */
    case GamePollsChoice = 'game_polls_choice';
    case GameTitleWaiting = 'game_title_waiting';
    case GameDescriptionWaiting = 'game_description_waiting';
    case GameTimeLimitWaiting = 'game_time_limit_waiting';
    case GameChannelWaiting = 'game_channel_waiting';
    case GameCreatedSuccessShow = 'game_created_success_show';
    case GamePlayersWaiting = 'game_players_waiting';
    case GameQuizProcess = 'game_quiz_process';

    /**
     * Channel
     * @deprecated
     * TODO: Need to create handler for sent success and create table. Create migration for adding states to DB
     */
    case ChannelPollsSentSuccess = 'channel_polls_sent_success';

    /** Account */
    case Account = 'account';
    case AccountReferralLinkShow = 'account_referral_link_show';
    case AccountReferredUsersShow = 'account_referred_users_show';

    /** Admin */
    case Admin = 'admin';
    case AdminNewsletterWaiting = 'admin_newsletter_waiting';
    case AdminNewsletterConfirmation = 'admin_newsletter_confirmation';
    case AdminNewsletterSentSuccess = 'admin_newsletter_sent_success';
    case AdminStatisticMenuChoice = 'admin_statistic_menu_choice';
    case AdminStatisticPollsMenuChoice = 'admin_statistic_polls_menu_choice';
    case AdminStatisticPollsPerYearShow = 'admin_statistic_polls_per_year_show';
    case AdminStatisticPollsPerQuarterShow = 'admin_statistic_polls_per_quarter_show';
    case AdminStatisticPollsPerMonthShow = 'admin_statistic_polls_per_month_show';
    case AdminStatisticPollsPerWeekShow = 'admin_statistic_polls_per_week_show';
    case AdminStatisticPollsPerDayShow = 'admin_statistic_polls_per_day_show';
    case AdminStatisticUsersMenuChoice = 'admin_statistic_users_menu_choice';
    case AdminStatisticUsersPerDayShow = 'admin_statistic_users_per_day_show';

    /** Help */
    case HELP = 'help';

    public function userState(RepositoryInterface $repository, TelegramService $telegramService): UserState
    {
        return match ($this) {
            /** Poll states */
            self::Start => new StartState($repository, $telegramService),
            self::PollSupport => new SupportState($repository, $telegramService),
            self::PollTypeChoice => new TypeChoiceState($repository, $telegramService),
            self::PollAnonymityChoice => new AnonymityChoiceState($repository, $telegramService),
            self::PollDifficultyChoice => new DifficultyChoiceState($repository, $telegramService),
            self::PollSectorChoice => new SectorChoiceState($repository, $telegramService),
            self::PollSubjectChoice => new SubjectChoiceState($repository, $telegramService),
            self::PollThemeWaiting => new ThemeWaitingState($repository, $telegramService),
            self::PollAiRespondedChoice => new AiRespondedChoiceState($repository, $telegramService),
            /** Game states */
            self::GamePollsChoice => new GamePollsChoiceState($repository, $telegramService),
            self::GameTitleWaiting => new GameTitleWaitingState($repository, $telegramService),
            self::GameDescriptionWaiting => new GameDescriptionWaitingState($repository, $telegramService),
            self::GameTimeLimitWaiting => new GameTimeLimitWaitingState($repository, $telegramService),
            self::GameChannelWaiting => new GameChannelWaitingState($repository, $telegramService),
            self::GameCreatedSuccessShow => new GameCreatedSuccessState($repository, $telegramService),

            self::GamePlayersWaiting => new GamePlayersWaitingState($repository, $telegramService),

            /** Account states */
            self::Account => new AccountState($repository, $telegramService),
            self::AccountReferralLinkShow => new ReferralLinkShowState($repository, $telegramService),
            self::AccountReferredUsersShow => new ReferredUsersShowState($repository, $telegramService),
            /** Admin states */
            self::Admin => new AdminState($repository, $telegramService),
            self::AdminNewsletterWaiting => new NewsletterWaitingState($repository, $telegramService),
            self::AdminNewsletterConfirmation => new NewsletterConfirmationState($repository, $telegramService),
            self::AdminNewsletterSentSuccess => new NewsletterSentSuccessState($repository, $telegramService),
            self::AdminStatisticMenuChoice => new StatisticMenuChoiceState($repository, $telegramService),
            self::AdminStatisticPollsMenuChoice => new StatisticPollsMenuChoiceState($repository, $telegramService),
            self::AdminStatisticPollsPerYearShow => new StatisticPollsPerYearShowState($repository, $telegramService),
            self::AdminStatisticPollsPerQuarterShow => new StatisticPollsPerQuarterShowState($repository, $telegramService),
            self::AdminStatisticPollsPerMonthShow => new StatisticPollsPerMonthShowState($repository, $telegramService),
            self::AdminStatisticPollsPerWeekShow => new StatisticPollsPerWeekShowState($repository, $telegramService),
            self::AdminStatisticPollsPerDayShow => new StatisticPollsPerDayShowState($repository, $telegramService),
            self::AdminStatisticUsersMenuChoice => new StatisticUsersMenuChoiceState($repository, $telegramService),
            self::AdminStatisticUsersPerDayShow => new StatisticUsersPerDayShowState($repository, $telegramService),
            /** Help states */
            self::HELP => new HelpState($repository, $telegramService),
        };
    }

    public function backState(): self
    {
        return match ($this) {
            self::Account,
            self::Admin,
            self::HELP,
            self::Start,
            self::PollSupport,
            self::PollTypeChoice,
            self::PollAiRespondedChoice,
            self::GamePollsChoice,
            self::GameCreatedSuccessShow,
            self::GamePlayersWaiting => self::Start,
            self::PollAnonymityChoice => self::PollTypeChoice,
            self::PollDifficultyChoice => self::PollAnonymityChoice,
            self::PollSectorChoice => self::PollDifficultyChoice,
            self::PollSubjectChoice => self::PollSectorChoice,
            self::PollThemeWaiting => self::PollSubjectChoice,
            self::GameTitleWaiting => self::GamePollsChoice,
            self::GameDescriptionWaiting => self::GameTitleWaiting,
            self::GameTimeLimitWaiting => self::GameDescriptionWaiting,
            self::GameChannelWaiting => self::GameTimeLimitWaiting,
            self::AccountReferralLinkShow,
            self::AccountReferredUsersShow => self::Account,
            self::AdminNewsletterWaiting,
            self::AdminNewsletterSentSuccess,
            self::AdminStatisticMenuChoice => self::Admin,
            self::AdminNewsletterConfirmation => self::AdminNewsletterWaiting,
            self::AdminStatisticPollsMenuChoice,
            self::AdminStatisticUsersMenuChoice => self::AdminStatisticMenuChoice,
            self::AdminStatisticPollsPerYearShow,
            self::AdminStatisticPollsPerQuarterShow,
            self::AdminStatisticPollsPerMonthShow,
            self::AdminStatisticPollsPerWeekShow,
            self::AdminStatisticPollsPerDayShow => self::AdminStatisticPollsMenuChoice,
            self::AdminStatisticUsersPerDayShow => self::AdminStatisticUsersMenuChoice,
        };
    }

    public function sender(RepositoryInterface $repository, TelegramService $telegramService, User $user): SenderInterface
    {
        return match ($this) {
            self::Account => new AccountSender($repository, $telegramService, $user),
            self::HELP => new HelpSender($repository, $telegramService, $user),
            self::Start => new StartSender($repository, $telegramService, $user),
            self::PollSupport => new SupportSender($repository, $telegramService, $user),
            self::PollTypeChoice => new TypeChoiceSender($repository, $telegramService, $user),
            self::PollAnonymityChoice => new AnonymityChoiceSender($repository, $telegramService, $user),
            self::PollDifficultyChoice => new DifficultyChoiceSender($repository, $telegramService, $user),
            self::PollSectorChoice => new SectorChoiceSender($repository, $telegramService, $user),
            self::PollSubjectChoice => new SubjectChoiceSender($repository, $telegramService, $user),
            self::PollThemeWaiting => new ThemeWaitingSender($repository, $telegramService, $user),
            self::PollAiRespondedChoice => new AiRespondedChoiceSender($repository, $telegramService, $user),

            self::GamePollsChoice => new GamePollsChoiceSender($repository, $telegramService, $user),
            self::GameTitleWaiting => new GameTitleWaitingSender($repository, $telegramService, $user),
            self::GameDescriptionWaiting => new GameDescriptionWaitingSender($repository, $telegramService, $user),
            self::GameTimeLimitWaiting => new GameTimeLimitWaitingSender($repository, $telegramService, $user),
            self::GameChannelWaiting => new GameChannelWaitingSender($repository, $telegramService, $user),
            self::GameCreatedSuccessShow => new GameCreatedSuccessShowSender($repository, $telegramService, $user),
            self::GamePlayersWaiting => new GamePlayersWaitingSender($repository, $telegramService, $user),

            self::AccountReferralLinkShow => new ReferralLinkShowSender($repository, $telegramService, $user),
            self::AccountReferredUsersShow => new ReferredUsersShowSender($repository, $telegramService, $user),
            self::Admin => new AdminSender($repository, $telegramService, $user),
            self::AdminNewsletterWaiting => new NewsletterWaitingSender($repository, $telegramService, $user),
            self::AdminNewsletterConfirmation => new NewsletterConfirmationSender($repository, $telegramService, $user),
            self::AdminNewsletterSentSuccess => new NewsletterSentSuccessSender($repository, $telegramService, $user),
            self::AdminStatisticMenuChoice => new StatisticMenuChoiceSender($repository, $telegramService, $user),
            self::AdminStatisticPollsMenuChoice => new StatisticPollsMenuChoiceSender($repository, $telegramService, $user),
            self::AdminStatisticPollsPerYearShow => new StatisticPollsPerYearShowSender($repository, $telegramService, $user),
            self::AdminStatisticPollsPerQuarterShow => new StatisticPollsPerQuarterShowSender($repository, $telegramService, $user),
            self::AdminStatisticPollsPerMonthShow => new StatisticPollsPerMonthShowSender($repository, $telegramService, $user),
            self::AdminStatisticPollsPerWeekShow => new StatisticPollsPerWeekShowSender($repository, $telegramService, $user),
            self::AdminStatisticPollsPerDayShow => new StatisticPollsPerDayShowSender($repository, $telegramService, $user),
            self::AdminStatisticUsersMenuChoice => new StatisticUsersMenuChoiceSender($repository, $telegramService, $user),
            self::AdminStatisticUsersPerDayShow => new StatisticUsersPerDayShowSender($repository, $telegramService, $user),
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::Start => "Привет! Выбери вариант:",
            self::PollTypeChoice => "Выберите тип опроса:",
            self::PollSupport => "Если у вас есть вопросы, напишите мне в личные сообщения: <a href='https://t.me/nkm_studio'>https://t.me/nkm_studio</a>",
            self::PollAnonymityChoice => "Опрос будет анонимный?",
            self::PollDifficultyChoice => "Выберите сложность вопросов:",
            self::PollSectorChoice => "Выберите направление:",
            self::PollSubjectChoice => "Выберите предмет:",
            self::PollThemeWaiting => "Введите свой вопрос:",
            self::PollAiRespondedChoice => "Выберите, что делать дальше:",

            self::GamePollsChoice => "Выберите, какие вопросы нужно отправить?",
            self::GameTitleWaiting => "Введите название вашей игры:",
            self::GameDescriptionWaiting => "Введите описание вашей игры:",
            self::GameTimeLimitWaiting => "Какой лимит времени в секундах Вы даете на ответ? Напишите только цифру.",
            self::GameChannelWaiting => "Напишите название канала или ссылку на канал:",
            self::GameCreatedSuccessShow => "Игра успешно создана! Теперь Вы можете отправить ее в канал.",
            self::GamePlayersWaiting => "Игра успешно отправлена в канал! Она начнется через 30 секунд.",

            self::Account => "Мой аккаунт:",
            self::AccountReferredUsersShow => "Ваши приглашенные пользователи:\n",

            self::Admin => "Меню администратора:",
            self::AdminNewsletterWaiting => "Введите сообщение и прикрепите файлы (если необходимо) для рассылки пользователям:\n\n❗️После отправки сообщения отменить или удалить его будет невозможно!!!",
            self::AdminNewsletterConfirmation => "❗️Внимательно проверьте Ваше сообщение!!! \n\nПосле подтверждения, это сообщение отправится всем подписчикам бота.",
            self::AdminNewsletterSentSuccess => "✅ Сообщение успешно разослано всем подписчикам бота!",
            self::AdminStatisticMenuChoice => "Статистика бота:",
            self::AdminStatisticPollsMenuChoice => "Статистика созданных тестов:",
            self::AdminStatisticPollsPerYearShow => "За последний год не было создано ни одного теста.",
            self::AdminStatisticPollsPerQuarterShow => "За последний квартал не было создано ни одного теста.",
            self::AdminStatisticPollsPerMonthShow => "За последний месяц не было создано ни одного теста.",
            self::AdminStatisticPollsPerWeekShow => "За последнюю неделю не было создано ни одного теста.",
            self::AdminStatisticPollsPerDayShow => "Сегодня тесты еще не создавались.",
            self::AdminStatisticUsersPerDayShow => "Новые пользователи сегодня не регистрировались.",
            self::AdminStatisticUsersMenuChoice => "Статистика пользователей:",

            self::HELP => "Инструкция по работе с ботом:\n\nДля того, чтобы Corgish-бот корректно составил тест, ответьте на вопросы бота и пройдите все шаги.\n\n/start - начать сначала\n/help - помощь и техподдержка",
        };
    }

    public function buttons(): array
    {
        return match ($this) {
            self::Start => [
                new ButtonDto(CallbackEnum::CreateSurvey->value, CallbackEnum::CreateSurvey->buttonText()),
                new ButtonDto(CallbackEnum::Support->value, CallbackEnum::Support->buttonText()),
            ],
            self::PollTypeChoice => [
                new ButtonDto(CallbackEnum::TypeQuiz->value, CallbackEnum::TypeQuiz->buttonText()),
                new ButtonDto(CallbackEnum::TypeSurvey->value, CallbackEnum::TypeSurvey->buttonText()),
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText()),
            ],
            self::PollSupport,
            self::PollThemeWaiting,
            self::GameTitleWaiting,
            self::GameDescriptionWaiting,
            self::GameTimeLimitWaiting,
            self::GameChannelWaiting,
            self::AdminStatisticPollsPerYearShow,
            self::AdminStatisticPollsPerQuarterShow,
            self::AdminStatisticPollsPerMonthShow,
            self::AdminStatisticPollsPerWeekShow,
            self::AdminStatisticPollsPerDayShow,
            self::AdminStatisticUsersPerDayShow,
            self::AccountReferralLinkShow,
            self::AccountReferredUsersShow,
            self::AdminNewsletterWaiting,
            self::HELP => [
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText())
            ],
            self::PollAnonymityChoice => [
                new ButtonDto(CallbackEnum::IsAnon->value, CallbackEnum::IsAnon->buttonText()),
                new ButtonDto(CallbackEnum::IsNotAnon->value, CallbackEnum::IsNotAnon->buttonText()),
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText()),
            ],
            self::PollDifficultyChoice => [
                new ButtonDto(CallbackEnum::LevelEasy->value, CallbackEnum::LevelEasy->buttonText()),
                new ButtonDto(CallbackEnum::LevelMiddle->value, CallbackEnum::LevelMiddle->buttonText()),
                new ButtonDto(CallbackEnum::LevelHard->value, CallbackEnum::LevelHard->buttonText()),
                new ButtonDto(CallbackEnum::LevelAny->value, CallbackEnum::LevelAny->buttonText()),
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText()),
            ],
            self::PollAiRespondedChoice => [
                new ButtonDto(CallbackEnum::RepeatFlow->value, CallbackEnum::RepeatFlow->buttonText()),
                new ButtonDto(CallbackEnum::GameCreate->value, CallbackEnum::GameCreate->buttonText()),
                new ButtonDto(CommandEnum::START->getCommand(), '↩️ Выбрать другую тему'),
            ],

            self::GameCreatedSuccessShow => [
                new ButtonDto(CallbackEnum::GameQuizStart->value, CallbackEnum::GameQuizStart->buttonText()),
                new ButtonDto(CommandEnum::START->getCommand(), "↩️ Вернуться в начало")
            ],
            self::GamePlayersWaiting => [
                new ButtonDto(CommandEnum::START->getCommand(), "↩️ Вернуться в начало")
            ],

            self::Account => [
                new ButtonDto(CallbackEnum::AccountReferredUsers->value, CallbackEnum::AccountReferredUsers->buttonText()),
                new ButtonDto(CallbackEnum::AccountReferralLink->value, CallbackEnum::AccountReferralLink->buttonText()),
                new ButtonDto(CommandEnum::START->getCommand(), "↩️ Вернуться в начало")
            ],

            self::Admin => [
                new ButtonDto(CallbackEnum::AdminNewsletterCreate->value, CallbackEnum::AdminNewsletterCreate->buttonText()),
                new ButtonDto(CallbackEnum::AdminStatisticMenu->value, CallbackEnum::AdminStatisticMenu->buttonText()),
                new ButtonDto(CommandEnum::START->getCommand(), '↩️ Вернуться в начало')
            ],
            self::AdminNewsletterConfirmation => [
                new ButtonDto(CallbackEnum::AdminNewsletterAccept->value, CallbackEnum::AdminNewsletterAccept->buttonText()),
                new ButtonDto(CallbackEnum::AdminNewsletterChange->value, CallbackEnum::AdminNewsletterChange->buttonText()),
            ],
            self::AdminNewsletterSentSuccess => [
                new ButtonDto(CommandEnum::ADMIN->value, '↩️ Вернуться в начало')
            ],
            self::AdminStatisticMenuChoice => [
                new ButtonDto(CallbackEnum::AdminStatisticPolls->value, CallbackEnum::AdminStatisticPolls->buttonText()),
                new ButtonDto(CallbackEnum::AdminStatisticUsers->value, CallbackEnum::AdminStatisticUsers->buttonText()),
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText())
            ],
            self::AdminStatisticPollsMenuChoice => [
                new ButtonDto(CallbackEnum::AdminStatisticPollsPerYear->value, CallbackEnum::AdminStatisticPollsPerYear->buttonText()),
                new ButtonDto(CallbackEnum::AdminStatisticPollsPerQuarter->value, CallbackEnum::AdminStatisticPollsPerQuarter->buttonText()),
                new ButtonDto(CallbackEnum::AdminStatisticPollsPerMonth->value, CallbackEnum::AdminStatisticPollsPerMonth->buttonText()),
                new ButtonDto(CallbackEnum::AdminStatisticPollsPerWeek->value, CallbackEnum::AdminStatisticPollsPerWeek->buttonText()),
                new ButtonDto(CallbackEnum::AdminStatisticPollsPerDay->value, CallbackEnum::AdminStatisticPollsPerDay->buttonText()),
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText())
            ],
            self::AdminStatisticUsersMenuChoice => [
                new ButtonDto(CallbackEnum::AdminStatisticUsersPerDay->value, CallbackEnum::AdminStatisticUsersPerDay->buttonText()),
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText())
            ],
        };
    }
}
