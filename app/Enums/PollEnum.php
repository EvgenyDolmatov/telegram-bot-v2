<?php

namespace App\Enums;

use App\Builder\MessageSender;
use App\Senders\Poll\TypeSelectSender;
use App\Senders\SenderInterface;
use App\Services\SenderService;
use App\Services\TelegramService;
use App\States\Poll\TypeSelectState;
use App\States\UserState;
use Illuminate\Http\Request;

enum PollEnum: string
{
    case TYPE_QUIZ = 'type_quiz';

    public function userState(Request $request, TelegramService $telegramService): UserState
    {
        return match ($this) {
            self::TYPE_QUIZ => new TypeSelectState($request, $telegramService),
        };
    }

    public function sender(
        Request $request,
        MessageSender $messageBuilder,
        SenderService $senderService
    ): SenderInterface {
        return match ($this) {
            self::TYPE_QUIZ => new TypeSelectSender($request, $messageBuilder, $senderService),
        };
    }
}
