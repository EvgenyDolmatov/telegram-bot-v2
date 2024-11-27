<?php

namespace App\Enums;

use App\Builder\MessageSender;
use App\Senders\Poll\TypeChoiceSender;
use App\Senders\SenderInterface;
use App\Services\SenderService;
use App\Services\TelegramService;
use App\States\Poll\TypeChoiceState;
use App\States\UserState;
use Illuminate\Http\Request;

enum PollEnum: string
{
    case CREATE_SURVEY = 'create_survey';
    case TYPE_QUIZ = 'type_quiz';

    public function state(): string
    {
        return match ($this) {
            self::CREATE_SURVEY => StateEnum::POLL_TYPE_CHOICE->value,
            self::TYPE_QUIZ => StateEnum::POLL_TYPE_CHOICE->value,
        };
    }
}
