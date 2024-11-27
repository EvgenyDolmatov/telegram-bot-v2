<?php

namespace App\Enums;

use App\Services\TelegramService;
use App\States\Account\AccountState;
use App\States\Admin\AdminState;
use App\States\Channel\ChannelState;
use App\States\Help\HelpState;
use App\States\StartState;
use App\States\UserState;
use Illuminate\Http\Request;

enum CommandEnum: string
{
    case ACCOUNT = 'account';
    case ADMIN = 'admin';
    case CHANNEL = 'channel';
    case HELP = 'help';
    case START = 'start';

    public function userState(Request $request, TelegramService $telegramService): UserState
    {
        return match ($this) {
            self::ACCOUNT => new AccountState($request, $telegramService),
            self::ADMIN => new AdminState($request, $telegramService),
            self::CHANNEL => new ChannelState($request, $telegramService),
            self::HELP => new HelpState($request, $telegramService),
            self::START => new StartState($request, $telegramService),
        };
    }
}
