<?php

namespace App\Enums;

use App\Constants\CommonConstants;
use App\Dto\ButtonDto;
use App\Models\User;
use App\Senders\Admin\NewsletterConfirmationSender;
use App\Senders\Admin\NewsletterWaitingSender;
use App\Senders\Commands\AccountSender;
use App\Senders\Commands\AdminSender;
use App\Senders\Commands\HelpSender;
use App\Senders\Commands\StartSender;
use App\Senders\Poll\AiRespondedChoiceSender;
use App\Senders\Poll\AnonymityChoiceSender;
use App\Senders\Poll\ChannelNameWaitingSender;
use App\Senders\Poll\DifficultyChoiceSender;
use App\Senders\Poll\ChannelPollsSentSuccessSender;
use App\Senders\Poll\ChannelPollsChoiceSender;
use App\Senders\Poll\SectorChoiceSender;
use App\Senders\Poll\SubjectChoiceSender;
use App\Senders\Poll\SupportSender;
use App\Senders\Poll\ThemeWaitingSender;
use App\Senders\Poll\TypeChoiceSender;
use App\Senders\SenderInterface;
use App\Services\TelegramService;
use App\States\Account\AccountState;
use App\States\Admin\AdminState;
use App\States\Admin\NewsletterConfirmationState;
use App\States\Admin\NewsletterWaitingState;
use App\States\Help\HelpState;
use App\States\Poll\AiRespondedChoiceState;
use App\States\Poll\AnonymityChoiceState;
use App\States\Poll\ChannelNameWaitingState;
use App\States\Poll\DifficultyChoiceState;
use App\States\Poll\ChannelPollsSentSuccessState;
use App\States\Poll\ChannelPollsChoiceState;
use App\States\Poll\SectorChoiceState;
use App\States\Poll\SubjectChoiceState;
use App\States\Poll\SupportState;
use App\States\Poll\ThemeWaitingState;
use App\States\Poll\TypeChoiceState;
use App\States\StartState;
use App\States\UserState;
use Illuminate\Http\Request;

enum StateEnum: string
{
    /** Poll */
    case START = 'start';
    case POLL_SUPPORT = 'poll_support';
    case POLL_TYPE_CHOICE = 'poll_type_choice';
    case POLL_ANONYMITY_CHOICE = 'poll_anonymity_choice';
    case POLL_DIFFICULTY_CHOICE = 'poll_difficulty_choice';
    case POLL_SECTOR_CHOICE = 'poll_sector_choice';
    case POLL_SUBJECT_CHOICE = 'poll_subject_choice';
    case POLL_THEME_WAITING = 'poll_theme_waiting';
    case POLL_AI_RESPONDED_CHOICE = 'poll_ai_responded_choice';

    /** Channel */
    case CHANNEL_POLLS_CHOICE = 'channel_polls_choice';
    case CHANNEL_NAME_WAITING = 'channel_name_waiting';
    case CHANNEL_POLLS_SENT_SUCCESS = 'channel_polls_sent_success';

    /** Account */
    case ACCOUNT = 'account';

    /** Admin */
    case ADMIN = 'admin';
    case ADMIN_NEWSLETTER_WAITING = 'admin_newsletter_waiting';
    case ADMIN_NEWSLETTER_CONFIRMATION = 'admin_newsletter_confirmation';
    case ADMIN_NEWSLETTER_SENT_SUCCESS = 'admin_newsletter_sent_success';

    /** Help */
    case HELP = 'help';

    public function userState(Request $request, TelegramService $telegramService): UserState
    {
        return match ($this) {
            /** Poll states */
            self::START => new StartState($request, $telegramService),
            self::POLL_SUPPORT => new SupportState($request, $telegramService),
            self::POLL_TYPE_CHOICE => new TypeChoiceState($request, $telegramService),
            self::POLL_ANONYMITY_CHOICE => new AnonymityChoiceState($request, $telegramService),
            self::POLL_DIFFICULTY_CHOICE => new DifficultyChoiceState($request, $telegramService),
            self::POLL_SECTOR_CHOICE => new SectorChoiceState($request, $telegramService),
            self::POLL_SUBJECT_CHOICE => new SubjectChoiceState($request, $telegramService),
            self::POLL_THEME_WAITING => new ThemeWaitingState($request, $telegramService),
            self::POLL_AI_RESPONDED_CHOICE => new AiRespondedChoiceState($request, $telegramService),
            /** Channel states */
            self::CHANNEL_POLLS_CHOICE => new ChannelPollsChoiceState($request, $telegramService),
            self::CHANNEL_NAME_WAITING => new ChannelNameWaitingState($request, $telegramService),
            self::CHANNEL_POLLS_SENT_SUCCESS => new ChannelPollsSentSuccessState($request, $telegramService),
            /** Account states */
            self::ACCOUNT => new AccountState($request, $telegramService),
            /** Admin states */
            self::ADMIN => new AdminState($request, $telegramService),
            self::ADMIN_NEWSLETTER_WAITING => new NewsletterWaitingState($request, $telegramService),
            self::ADMIN_NEWSLETTER_CONFIRMATION => new NewsletterConfirmationState($request, $telegramService),
            /** Help states */
            self::HELP => new HelpState($request, $telegramService),
        };
    }

    public function backState(): self
    {
        return match ($this) {
            self::ACCOUNT,
            self::ADMIN,
            self::HELP,
            self::START,
            self::POLL_SUPPORT,
            self::POLL_TYPE_CHOICE,
            self::POLL_AI_RESPONDED_CHOICE,
            self::CHANNEL_POLLS_CHOICE,
            self::CHANNEL_POLLS_SENT_SUCCESS => self::START,
            self::POLL_ANONYMITY_CHOICE => self::POLL_TYPE_CHOICE,
            self::POLL_DIFFICULTY_CHOICE => self::POLL_ANONYMITY_CHOICE,
            self::POLL_SECTOR_CHOICE => self::POLL_DIFFICULTY_CHOICE,
            self::POLL_SUBJECT_CHOICE => self::POLL_SECTOR_CHOICE,
            self::POLL_THEME_WAITING => self::POLL_SUBJECT_CHOICE,
            self::CHANNEL_NAME_WAITING => self::CHANNEL_POLLS_CHOICE,
            self::ADMIN_NEWSLETTER_WAITING => self::ADMIN,
            self::ADMIN_NEWSLETTER_CONFIRMATION => self::ADMIN_NEWSLETTER_WAITING,
        };
    }

    public function sender(Request $request, TelegramService $telegramService, User $user): SenderInterface
    {
        return match ($this) {
            self::ACCOUNT => new AccountSender($request, $telegramService, $user),
            self::HELP => new HelpSender($request, $telegramService, $user),
            self::START => new StartSender($request, $telegramService, $user),
            self::POLL_SUPPORT => new SupportSender($request, $telegramService, $user),
            self::POLL_TYPE_CHOICE => new TypeChoiceSender($request, $telegramService, $user),
            self::POLL_ANONYMITY_CHOICE => new AnonymityChoiceSender($request, $telegramService, $user),
            self::POLL_DIFFICULTY_CHOICE => new DifficultyChoiceSender($request, $telegramService, $user),
            self::POLL_SECTOR_CHOICE => new SectorChoiceSender($request, $telegramService, $user),
            self::POLL_SUBJECT_CHOICE => new SubjectChoiceSender($request, $telegramService, $user),
            self::POLL_THEME_WAITING => new ThemeWaitingSender($request, $telegramService, $user),
            self::POLL_AI_RESPONDED_CHOICE => new AiRespondedChoiceSender($request, $telegramService, $user),
            self::CHANNEL_POLLS_CHOICE => new ChannelPollsChoiceSender($request, $telegramService, $user),
            self::CHANNEL_NAME_WAITING => new ChannelNameWaitingSender($request, $telegramService, $user),
            self::CHANNEL_POLLS_SENT_SUCCESS => new ChannelPollsSentSuccessSender($request, $telegramService, $user),
            self::ADMIN => new AdminSender($request, $telegramService, $user),
            self::ADMIN_NEWSLETTER_WAITING => new NewsletterWaitingSender($request, $telegramService, $user),
            self::ADMIN_NEWSLETTER_CONFIRMATION => new NewsletterConfirmationSender($request, $telegramService, $user),
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::START => "Привет! Выбери вариант:",
            self::POLL_TYPE_CHOICE => "Выберите тип опроса:",
            self::POLL_SUPPORT => "Если у вас есть вопросы, напишите мне в личные сообщения: <a href='https://t.me/nkm_studio'>https://t.me/nkm_studio</a>",
            self::POLL_ANONYMITY_CHOICE => "Опрос будет анонимный?",
            self::POLL_DIFFICULTY_CHOICE => "Выберите сложность вопросов:",
            self::POLL_SECTOR_CHOICE => "Выберите направление:",
            self::POLL_SUBJECT_CHOICE => "Выберите предмет:",
            self::POLL_THEME_WAITING => "Введите свой вопрос:",
            self::POLL_AI_RESPONDED_CHOICE => "Выберите, что делать дальше:",
            self::CHANNEL_POLLS_CHOICE => "Выберите, какие вопросы нужно отправить?",
            self::CHANNEL_NAME_WAITING => "Напишите название канала или ссылку на канал:",
            self::CHANNEL_POLLS_SENT_SUCCESS => "Выбранные тесты успешно отправлены в канал.",

            self::ACCOUNT => "Мой аккаунт:",

            self::ADMIN => "Меню администратора:",
            self::ADMIN_NEWSLETTER_WAITING => "Введите сообщение и прикрепите файлы (если необходимо) для рассылки пользователям:\n\n❗️После отправки сообщения отменить или удалить его будет невозможно!!!",
            self::ADMIN_NEWSLETTER_CONFIRMATION => "Внимательно проверьте Ваше сообщение!!! \n\nПосле подтверждения, это сообщение отправится всем подписчикам бота.",

            self::HELP => "Инструкция по работе с ботом:\n\nДля того, чтобы Corgish-бот корректно составил тест, ответьте на вопросы бота и пройдите все шаги.\n\n/start - начать сначала\n/help - помощь и техподдержка",
        };
    }

    public function buttons(): array
    {
        return match ($this) {
            self::START => [
                new ButtonDto(CallbackEnum::CREATE_SURVEY->value, CallbackEnum::CREATE_SURVEY->buttonText()),
                new ButtonDto(CommonCallbackEnum::SUPPORT->value, 'Поддержка'),
            ],
            self::POLL_TYPE_CHOICE => [
                new ButtonDto(CallbackEnum::TYPE_QUIZ->value, CallbackEnum::TYPE_QUIZ->buttonText()),
                new ButtonDto(CallbackEnum::TYPE_SURVEY->value, CallbackEnum::TYPE_SURVEY->buttonText()),
                new ButtonDto(CommonConstants::BACK, "Назад"),
            ],
            self::POLL_SUPPORT,
            self::POLL_THEME_WAITING,
            self::CHANNEL_NAME_WAITING,
            self::ADMIN_NEWSLETTER_WAITING,
            self::HELP => [
                new ButtonDto(CommonConstants::BACK, "Назад")
            ],
            self::POLL_ANONYMITY_CHOICE => [
                new ButtonDto(CallbackEnum::IS_ANON->value, CallbackEnum::IS_ANON->buttonText()),
                new ButtonDto(CallbackEnum::IS_NOT_ANON->value, CallbackEnum::IS_NOT_ANON->buttonText()),
                new ButtonDto(CommonConstants::BACK, "Назад"),
            ],
            self::POLL_DIFFICULTY_CHOICE => [
                new ButtonDto(CallbackEnum::LEVEL_EASY->value, CallbackEnum::LEVEL_EASY->buttonText()),
                new ButtonDto(CallbackEnum::LEVEL_MIDDLE->value, CallbackEnum::LEVEL_MIDDLE->buttonText()),
                new ButtonDto(CallbackEnum::LEVEL_HARD->value, CallbackEnum::LEVEL_HARD->buttonText()),
                new ButtonDto(CallbackEnum::LEVEL_ANY->value, CallbackEnum::LEVEL_ANY->buttonText()),
                new ButtonDto(CommonConstants::BACK, "Назад")
            ],
            self::POLL_AI_RESPONDED_CHOICE => [
                new ButtonDto(CommandEnum::START->getCommand(), 'Выбрать другую тему'),
                new ButtonDto(CallbackEnum::REPEAT_FLOW->value, CallbackEnum::REPEAT_FLOW->buttonText()),
                new ButtonDto(CallbackEnum::SEND_TO_CHANNEL->value, CallbackEnum::SEND_TO_CHANNEL->buttonText()),
            ],
            self::CHANNEL_POLLS_SENT_SUCCESS => [
                new ButtonDto(CommandEnum::START->getCommand(), "Вернуться в начало")
            ],

            self::ADMIN => [
                new ButtonDto(CallbackEnum::ADMIN_NEWSLETTER_CREATE->value, CallbackEnum::ADMIN_NEWSLETTER_CREATE->buttonText()),
                new ButtonDto(CallbackEnum::ADMIN_STATISTIC_MENU->value, CallbackEnum::ADMIN_STATISTIC_MENU->buttonText()),
                new ButtonDto(CommandEnum::START->getCommand(), 'Вернуться в начало')
            ],
            self::ADMIN_NEWSLETTER_CONFIRMATION => [
                new ButtonDto(CallbackEnum::ADMIN_NEWSLETTER_ACCEPT->value, CallbackEnum::ADMIN_NEWSLETTER_ACCEPT->buttonText()),
                new ButtonDto(CallbackEnum::ADMIN_NEWSLETTER_CHANGE->value, CallbackEnum::ADMIN_NEWSLETTER_CHANGE->buttonText()),
            ]
        };
    }
}
