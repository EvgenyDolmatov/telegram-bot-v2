<?php

namespace App\Enums;

use App\Models\User;
use App\Senders\Commands\AccountSender;
use App\Senders\Commands\AdminSender;
use App\Senders\Commands\ChannelSender;
use App\Senders\Commands\HelpSender;
use App\Senders\Commands\StartSender;
use App\Senders\Poll\AiRespondedChoiceSender;
use App\Senders\Poll\AnonymityChoiceSender;
use App\Senders\Poll\DifficultyChoiceSender;
use App\Senders\Poll\SectorChoiceSender;
use App\Senders\Poll\SubjectChoiceSender;
use App\Senders\Poll\ThemeWaitingSender;
use App\Senders\Poll\TypeChoiceSender;
use App\Senders\SenderInterface;
use App\Services\TelegramService;
use App\States\Account\AccountState;
use App\States\Admin\AdminState;
use App\States\Channel\ChannelState;
use App\States\Help\HelpState;
use App\States\Poll\AiRespondedChoiceState;
use App\States\Poll\AnonymityChoiceState;
use App\States\Poll\DifficultyChoiceState;
use App\States\Poll\SectorChoiceState;
use App\States\Poll\SubjectChoiceState;
use App\States\Poll\ThemeWaitingState;
use App\States\Poll\TypeChoiceState;
use App\States\StartState;
use App\States\UserState;
use Illuminate\Http\Request;

enum StateEnum: string
{
//    case ADMIN_START = 'admin_start';

    case ACCOUNT = 'account';
    case ADMIN = 'admin';
    case CHANNEL = 'channel';
    case HELP = 'help';
    case START = 'start';
    case POLL_TYPE_CHOICE = 'poll_type_choice';
    case POLL_ANONYMITY_CHOICE = 'poll_anonymity_choice';
    case POLL_DIFFICULTY_CHOICE = 'poll_difficulty_choice';
    case POLL_SECTOR_CHOICE = 'poll_sector_choice';
    case POLL_SUBJECT_CHOICE = 'poll_subject_choice';
    case POLL_THEME_WAITING = 'poll_theme_waiting';
    case POLL_AI_RESPONDED_CHOICE = 'poll_ai_responded_choice';

    public function userState(Request $request, TelegramService $telegramService): UserState
    {
        return match ($this) {
            self::ACCOUNT => new AccountState($request, $telegramService),
            self::ADMIN => new AdminState($request, $telegramService),
            self::CHANNEL => new ChannelState($request, $telegramService),
            self::HELP => new HelpState($request, $telegramService),
            self::START => new StartState($request, $telegramService),
            self::POLL_TYPE_CHOICE => new TypeChoiceState($request, $telegramService),
            self::POLL_ANONYMITY_CHOICE => new AnonymityChoiceState($request, $telegramService),
            self::POLL_DIFFICULTY_CHOICE => new DifficultyChoiceState($request, $telegramService),
            self::POLL_SECTOR_CHOICE => new SectorChoiceState($request, $telegramService),
            self::POLL_SUBJECT_CHOICE => new SubjectChoiceState($request, $telegramService),
            self::POLL_THEME_WAITING => new ThemeWaitingState($request, $telegramService),
            self::POLL_AI_RESPONDED_CHOICE => new AiRespondedChoiceState($request, $telegramService),
        };
    }

    public function sender(Request $request, TelegramService $telegramService, User $user): SenderInterface
    {
        return match ($this) {
            self::ACCOUNT => new AccountSender($request, $telegramService, $user),
            self::ADMIN => new AdminSender($request, $telegramService, $user),
            self::CHANNEL => new ChannelSender($request, $telegramService, $user),
            self::HELP => new HelpSender($request, $telegramService, $user),
            self::START => new StartSender($request, $telegramService, $user),
            self::POLL_TYPE_CHOICE => new TypeChoiceSender($request, $telegramService, $user),
            self::POLL_ANONYMITY_CHOICE => new AnonymityChoiceSender($request, $telegramService, $user),
            self::POLL_DIFFICULTY_CHOICE => new DifficultyChoiceSender($request, $telegramService, $user),
            self::POLL_SECTOR_CHOICE => new SectorChoiceSender($request, $telegramService, $user),
            self::POLL_SUBJECT_CHOICE => new SubjectChoiceSender($request, $telegramService, $user),
            self::POLL_THEME_WAITING => new ThemeWaitingSender($request, $telegramService, $user),
            self::POLL_AI_RESPONDED_CHOICE => new AiRespondedChoiceSender($request, $telegramService, $user),
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::START => "Привет! Выбери вариант:",
            self::POLL_TYPE_CHOICE => "Выберите тип опроса:",
            self::POLL_ANONYMITY_CHOICE => "Опрос будет анонимный?",
            self::POLL_DIFFICULTY_CHOICE => "Выберите сложность вопросов:",
            self::POLL_SECTOR_CHOICE => "Выберите направление:",
            self::POLL_SUBJECT_CHOICE => "Выберите предмет:",
            self::POLL_THEME_WAITING => "Введите свой вопрос:",
            self::POLL_AI_RESPONDED_CHOICE => "Подождите. Ваш запрос обрабатывается...",

            self::ACCOUNT => "Мой аккаунт:",
            self::ADMIN => "Меню администратора:",
            self::CHANNEL => "... Channel ...",
            self::HELP => "Если у вас есть вопросы, напишите мне в личные сообщения: <a href='https://t.me/nkm_studio'>https://t.me/nkm_studio</a>",
        };
    }
}
