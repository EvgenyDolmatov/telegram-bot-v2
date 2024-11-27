<?php

namespace App\Enums;

use App\Builder\MessageSender;
use App\Senders\Commands\AccountSender;
use App\Senders\Commands\AdminSender;
use App\Senders\Commands\ChannelSender;
use App\Senders\Commands\HelpSender;
use App\Senders\Commands\StartSender;
use App\Senders\Poll\TypeChoiceSender;
use App\Senders\SenderInterface;
use App\Services\SenderService;
use App\Services\TelegramService;
use App\States\Account\AccountState;
use App\States\Admin\AdminState;
use App\States\Channel\ChannelState;
use App\States\Help\HelpState;
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

    public function userState(Request $request, TelegramService $telegramService): UserState
    {
        return match ($this) {
            self::ACCOUNT => new AccountState($request, $telegramService),
            self::ADMIN => new AdminState($request, $telegramService),
            self::CHANNEL => new ChannelState($request, $telegramService),
            self::HELP => new HelpState($request, $telegramService),
            self::START => new StartState($request, $telegramService),
            self::POLL_TYPE_CHOICE => new TypeChoiceState($request, $telegramService),
        };
    }

    public function sender(
        Request $request,
        MessageSender $messageBuilder,
        SenderService $senderService
    ): SenderInterface {
        return match ($this) {
            self::ACCOUNT => new AccountSender($request, $messageBuilder, $senderService),
            self::ADMIN => new AdminSender($request, $messageBuilder, $senderService),
            self::CHANNEL => new ChannelSender($request, $messageBuilder, $senderService),
            self::HELP => new HelpSender($request, $messageBuilder, $senderService),
            self::START => new StartSender($request, $messageBuilder, $senderService),
            self::POLL_TYPE_CHOICE => new TypeChoiceSender($request, $messageBuilder, $senderService),
        };
    }
}
