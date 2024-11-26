<?php

namespace App\Enums;

use App\Services\TelegramService;
use App\States\AccountState;
use App\States\StartState;
use App\States\UserState;
use Illuminate\Http\Request;

enum CommandEnum: string
{
    case ACCOUNT = '/account';
    case ADMIN = '/admin';
    case HELP = '/help';
    case START = '/start';
    case CHANNEL = '/channel';

    public function userState(Request $request, TelegramService $telegramService): UserState
    {
        return match ($this) {
            self::ACCOUNT => new AccountState($request, $telegramService),
            self::START => new StartState($request, $telegramService),
            self::ADMIN => new StartState($request, $telegramService),
            self::HELP => new StartState($request, $telegramService),
            self::CHANNEL => new StartState($request, $telegramService),
        };
    }
}
