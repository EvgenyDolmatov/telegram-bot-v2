<?php

namespace App\Enums;

use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Enums\Callback\GameEnum;
use App\Enums\Callback\GameplayEnum;
use App\Enums\Callback\PollEnum;
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
use App\Senders\Game\Edit\GameEditPollsChoiceSender;
use App\Senders\Game\Edit\GameEditTimeLimitChoiceSender;
use App\Senders\Game\Edit\GameEditTitleWaitingSender;
use App\Senders\Game\GameCreatedMenuShowSender;
use App\Senders\Game\GameEditMenuShowSender;
use App\Senders\Game\GamePollsChoiceSender;
use App\Senders\Game\GameTimeLimitChoiceSender;
use App\Senders\Game\GameTitleWaitingSender;
use App\Senders\Gameplay\GameplayCountdownShowSender;
use App\Senders\Gameplay\GameplayWaitingToStartSender;
use App\Senders\Poll\AfterAiRespondedChoiceSender;
use App\Senders\Poll\AiRespondedChoiceSender;
use App\Senders\Poll\RequestWaitingSender;
use App\Senders\Poll\SupportSender;
use App\Senders\Poll\ThemeChoiceSender;
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
use App\States\Game\Edit\GameEditPollsChoiceState;
use App\States\Game\Edit\GameEditTimeLimitWaitingState;
use App\States\Game\Edit\GameEditTitleWaitingState;
use App\States\Game\GameCreatedMenuShowState;
use App\States\Game\GameEditMenuShowState;
use App\States\Game\GamePollsChoiceState;
use App\States\Game\GameTimeLimitWaitingState;
use App\States\Game\GameTitleWaitingState;
use App\States\Gameplay\GameplayCountdownShowState;
use App\States\Gameplay\GameplayWaitingToStartState;
use App\States\Help\HelpState;
use App\States\Poll\AfterAiRespondedChoiceState;
use App\States\Poll\AiRespondedChoiceState;
use App\States\Poll\RequestWaitingState;
use App\States\Poll\SupportState;
use App\States\Poll\ThemeChoiceState;
use App\States\Poll\TypeChoiceState;
use App\States\StartState;
use App\States\UserState;

enum StateEnum: string
{
    /** Common */
    case Start = 'start';
    case PollSupport = 'poll_support';

    /** Poll */
    case PollTypeChoice = 'poll_type_choice';
    case PollThemeChoice = 'poll_theme_choice';
    case PollRequestWaiting = 'poll_request_waiting';
    case PollAiRespondedChoice = 'poll_ai_responded_choice';
    case PollAfterAiRespondedChoice = 'poll_after_ai_responded_choice';

    /** Game */
    case GameTitleWaiting = 'game_title_waiting';
    case GamePollsChoice = 'game_polls_choice';
    case GameTimeLimitChoice = 'game_time_limit_choice';
    case GameCreatedMenuShow = 'game_created_menu_show';
    case GameEditMenuShow = 'game_edit_menu_show';
    case GameEditTitleWaiting = 'game_edit_title_waiting';
    case GameEditPollsChoice = 'game_edit_polls_choice';
    case GameEditTimeLimitChoice = 'game_edit_time_limit_choice';
    case GameChangedTitleAction = 'game_changed_title_action';
    case GameAddToCommunityAction = 'game_add_to_community_action';
    case GameInvitationLinkShow = 'game_invitation_link_show';
    case GameStatisticShow = 'game_statistic_show';

    /** Gameplay */
    case GameplayWaitingToStart = 'gameplay_waiting_to_start';
    case GameplayCountdownShow = 'gameplay_countdown_show';


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
    case Help = 'help';

    public function userState(RepositoryInterface $repository, TelegramService $telegramService): UserState
    {
        return match ($this) {
            /** Common states */
            self::Start => new StartState($repository, $telegramService),
            self::PollSupport => new SupportState($repository, $telegramService),

            /** Poll states */
            self::PollTypeChoice => new TypeChoiceState($repository, $telegramService),
            self::PollThemeChoice => new ThemeChoiceState($repository, $telegramService),
            self::PollRequestWaiting => new RequestWaitingState($repository, $telegramService),
            self::PollAiRespondedChoice => new AiRespondedChoiceState($repository, $telegramService),
            self::PollAfterAiRespondedChoice => new AfterAiRespondedChoiceState($repository, $telegramService),

            /** Game states */
            self::GameTitleWaiting => new GameTitleWaitingState($repository, $telegramService),
            self::GamePollsChoice => new GamePollsChoiceState($repository, $telegramService),
            self::GameTimeLimitChoice => new GameTimeLimitWaitingState($repository, $telegramService),
            self::GameCreatedMenuShow => new GameCreatedMenuShowState($repository, $telegramService),
            self::GameEditMenuShow => new GameEditMenuShowState($repository, $telegramService),
            self::GameEditTitleWaiting => new GameEditTitleWaitingState($repository, $telegramService),
            self::GameEditPollsChoice => new GameEditPollsChoiceState($repository, $telegramService),
            self::GameEditTimeLimitChoice => new GameEditTimeLimitWaitingState($repository, $telegramService),
            /** Gameplay states */
            self::GameplayWaitingToStart => new GameplayWaitingToStartState($repository, $telegramService),
            self::GameplayCountdownShow => new GameplayCountdownShowState($repository, $telegramService),



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
            self::Help => new HelpState($repository, $telegramService),
        };
    }

    public function backState(): self
    {
        return match ($this) {
            self::Account,
            self::Admin,
            self::Help,
            self::Start,
            self::PollSupport,
            self::PollTypeChoice,
            self::PollAiRespondedChoice,
            self::PollAfterAiRespondedChoice,
            self::GameCreatedMenuShow,
            self::PollThemeChoice => self::PollTypeChoice,
            self::PollRequestWaiting => self::PollThemeChoice,
            /** Game */
            self::GameTitleWaiting => self::PollAfterAiRespondedChoice,
            self::GamePollsChoice => self::GameTitleWaiting,
            self::GameTimeLimitChoice => self::GamePollsChoice,
            self::GameEditMenuShow => self::GameCreatedMenuShow,
            self::GameEditTitleWaiting,
            self::GameEditPollsChoice,
            self::GameEditTimeLimitChoice => self::GameEditMenuShow,



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
            /** Common senders */
            self::Start => new StartSender($repository, $telegramService, $user),
            self::PollSupport => new SupportSender($repository, $telegramService, $user),
            self::Help => new HelpSender($repository, $telegramService, $user),

            /** Poll senders */
            self::PollTypeChoice => new TypeChoiceSender($repository, $telegramService, $user),
            self::PollThemeChoice => new ThemeChoiceSender($repository, $telegramService, $user),
            self::PollRequestWaiting => new RequestWaitingSender($repository, $telegramService, $user),
            self::PollAiRespondedChoice => new AiRespondedChoiceSender($repository, $telegramService, $user),
            self::PollAfterAiRespondedChoice => new AfterAiRespondedChoiceSender($repository, $telegramService, $user),

            /** Game senders */
            self::GameTitleWaiting => new GameTitleWaitingSender($repository, $telegramService, $user),
            self::GamePollsChoice => new GamePollsChoiceSender($repository, $telegramService, $user),
            self::GameTimeLimitChoice => new GameTimeLimitChoiceSender($repository, $telegramService, $user),
            self::GameCreatedMenuShow => new GameCreatedMenuShowSender($repository, $telegramService, $user),
            self::GameEditMenuShow => new GameEditMenuShowSender($repository, $telegramService, $user),
            self::GameEditTitleWaiting => new GameEditTitleWaitingSender($repository, $telegramService, $user),
            self::GameEditPollsChoice => new GameEditPollsChoiceSender($repository, $telegramService, $user),
            self::GameEditTimeLimitChoice => new GameEditTimeLimitChoiceSender($repository, $telegramService, $user),

            /** Gameplay senders */
            self::GameplayWaitingToStart => new GameplayWaitingToStartSender($repository, $telegramService, $user),
            self::GameplayCountdownShow => new GameplayCountdownShowSender($repository, $telegramService, $user),

            /** Account senders */
            self::Account => new AccountSender($repository, $telegramService, $user),
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
            /** Common titles */
            self::Start => "<b>Это бот Corgish.</b>\n\nВы можете создавать и проводить многопользовательские онлайн-викторины и опросы с помощью Corgish AI или вручную.\n\n<b>Бот умеет:</b>\n- создавать онлайн-викторины,\n- отправлять викторины в группы,\n- выдавать статистику.\n\nСписок команд:",
            self::PollSupport => "Если у вас есть вопросы, напишите мне в личные сообщения: <a href='https://t.me/nkm_studio'>https://t.me/nkm_studio</a>",

            /** Poll titles */
            self::PollTypeChoice => "<b>Викторина</b>\nМногопользовательская игра\n\n<b>Опрос</b>\nСоздаст список вопросов для исследования вашей аудитории\n\nВыберите тип игры:",
            self::PollThemeChoice => "<b>Выберите тему:</b>\n\n- Образование — /education\n- Игры — /games\n- Кино — /movies\n- Спорт — /sports\n- Музыка — /music\n- Технологии — /tech\n- Наука — /science\n- Здоровье — /health\n- Еда — /food\n- Путешествия — /travel\n- Искусство — /art\n- Мода — /fashion\n- История — /history\n- Литература — /books\n- Финансы — /finance\n- Автомобили — /cars\n- Дом и сад — /home\n- Животные — /pets\n- Новости — /news\n- Развлечения — /fun",
            self::PollRequestWaiting => "<b>Введите запрос:</b>\n<b>Например для темы «Игры»:</b>\n\nРоблокс, Mega Hide and Seak, Фишки и скрытые эффекты.\n\nℹ️ От точности формулировки зависит результат вопросов и ответов.",
            self::PollAiRespondedChoice => "Выберите, что делать дальше:",
            self::PollAfterAiRespondedChoice => "<b>Вы можете создать викторину из созданных вопросов.</b>\n\nНажмите кнопку «Создать викторину», выберите вопросы, на которые будут отвечать участники",

            /** Game titles */
            self::GameTitleWaiting,
            self::GameEditTitleWaiting => "<b>Введите название викторины</b>\n\nНапример, викторина для моей группы",
            self::GamePollsChoice,
            self::GameEditPollsChoice => "Выберите вопросы",
            self::GameTimeLimitChoice,
            self::GameEditTimeLimitChoice => "Укажите время для ответа пользователей",
            self::GameCreatedMenuShow => "Игра успешно создана!",
            self::GameEditMenuShow => "Выберите действие",

            /** Gameplay titles */
            self::GameplayWaitingToStart => "Game Start",


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

            self::Help => "Инструкция по работе с ботом:\n\nДля того, чтобы Corgish-бот корректно составил тест, ответьте на вопросы бота и пройдите все шаги.\n\n/start - начать сначала\n/help - помощь и техподдержка",
        };
    }

    public function buttons(): array
    {
        return match ($this) {
            /** Common buttons */
            self::Start => [
                new ButtonDto(CallbackEnum::CreateSurveyWithAi->value, CallbackEnum::CreateSurveyWithAi->buttonText()),
                new ButtonDto(CallbackEnum::Support->value, CallbackEnum::Support->buttonText()),
//                new ButtonDto(
//                    callbackData: "",
//                    text: "Call groups list",
//                    url: 'https://t.me/DevTest067Bot?startgroup=start'
//                ), // TODO: Do not forget to remove it.
            ],
            self::PollSupport => [
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText())
            ],

            /** Poll buttons */
            self::PollTypeChoice => [
                PollEnum::TypeQuiz->getButtonDto(),
                PollEnum::TypeSurvey->getButtonDto(),
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText()),
            ],
            self::PollThemeChoice,
            self::PollRequestWaiting => [
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText()),
            ],
            self::PollAiRespondedChoice => [
                PollEnum::RepeatFlow->getButtonDto(),
                new ButtonDto(CommandEnum::Start->getCommand(), '↩️ Выбрать другую тему'),
                PollEnum::AfterAiRespondedMenu->getButtonDto(),
            ],
            self::PollAfterAiRespondedChoice => [
                GameEnum::Create->getButtonDto(),
                new ButtonDto(CommandEnum::Start->getCommand(), "Отменить и выйти")
            ],

            /** Game buttons */
            self::GameTitleWaiting => [
                new ButtonDto(CommandEnum::Start->getCommand(), "Отменить и выйти")
            ],
            self::GamePollsChoice => [
                GameEnum::PollsSave->getButtonDto(value: "Отправить выбранные"),
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText())
            ],
            self::GameTimeLimitChoice => [
                GameEnum::TimeLimit15->getButtonDto(),
                GameEnum::TimeLimit20->getButtonDto(),
                GameEnum::TimeLimit25->getButtonDto(),
                GameEnum::TimeLimit30->getButtonDto(),
                GameEnum::TimeLimit45->getButtonDto(),
                GameEnum::TimeLimit60->getButtonDto(),
                GameEnum::TimeLimit180->getButtonDto(),
                GameEnum::TimeLimit300->getButtonDto(),
                GameEnum::TimeLimit600->getButtonDto(),
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText())
            ],
            self::GameCreatedMenuShow => [
                GameEnum::Edit->getButtonDto(),
                GameEnum::AddToCommunity->getButtonDto(),
                GameEnum::InvitationLink->getButtonDto(),
                GameEnum::Start->getButtonDto(),
                GameEnum::Statistics->getButtonDto(),
            ],
            self::GameEditMenuShow => [
                GameEnum::EditTitle->getButtonDto(),
                GameEnum::EditPolls->getButtonDto(),
                GameEnum::EditTimeLimit->getButtonDto(),
                new ButtonDto(CallbackEnum::Back->value, "Отмена")
            ],
            self::GameEditTitleWaiting => [
                new ButtonDto(CallbackEnum::Back->value, "Отмена")
            ],
            self::GameEditTimeLimitChoice => [
                GameEnum::TimeLimit15->getButtonDto(),
                GameEnum::TimeLimit20->getButtonDto(),
                GameEnum::TimeLimit25->getButtonDto(),
                GameEnum::TimeLimit30->getButtonDto(),
                GameEnum::TimeLimit45->getButtonDto(),
                GameEnum::TimeLimit60->getButtonDto(),
                GameEnum::TimeLimit180->getButtonDto(),
                GameEnum::TimeLimit300->getButtonDto(),
                GameEnum::TimeLimit600->getButtonDto(),
                new ButtonDto(CallbackEnum::Back->value, "Отмена")
            ],

            /** Gameplay buttons */
            self::GameplayWaitingToStart => [
                GameplayEnum::Start->getButtonDto(),
            ],


            self::AdminStatisticPollsPerYearShow,
            self::AdminStatisticPollsPerQuarterShow,
            self::AdminStatisticPollsPerMonthShow,
            self::AdminStatisticPollsPerWeekShow,
            self::AdminStatisticPollsPerDayShow,
            self::AdminStatisticUsersPerDayShow,
            self::AccountReferralLinkShow,
            self::AccountReferredUsersShow,
            self::AdminNewsletterWaiting,
            self::Help => [
                new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText())
            ],

            self::Account => [
                new ButtonDto(CallbackEnum::AccountReferredUsers->value, CallbackEnum::AccountReferredUsers->buttonText()),
                new ButtonDto(CallbackEnum::AccountReferralLink->value, CallbackEnum::AccountReferralLink->buttonText()),
                new ButtonDto(CommandEnum::Start->getCommand(), "↩️ Вернуться в начало")
            ],

            self::Admin => [
                new ButtonDto(CallbackEnum::AdminNewsletterCreate->value, CallbackEnum::AdminNewsletterCreate->buttonText()),
                new ButtonDto(CallbackEnum::AdminStatisticMenu->value, CallbackEnum::AdminStatisticMenu->buttonText()),
                new ButtonDto(CommandEnum::Start->getCommand(), '↩️ Вернуться в начало')
            ],
            self::AdminNewsletterConfirmation => [
                new ButtonDto(CallbackEnum::AdminNewsletterAccept->value, CallbackEnum::AdminNewsletterAccept->buttonText()),
                new ButtonDto(CallbackEnum::AdminNewsletterChange->value, CallbackEnum::AdminNewsletterChange->buttonText()),
            ],
            self::AdminNewsletterSentSuccess => [
                new ButtonDto(CommandEnum::Admin->value, '↩️ Вернуться в начало')
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
