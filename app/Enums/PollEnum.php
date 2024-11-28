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
    case TYPE_SURVEY = 'type_survey';
    case IS_ANON = 'is_anon';
    case IS_NOT_ANON = 'is_not_anon';
    case LEVEL_EASY = 'level_easy';
    case LEVEL_MIDDLE = 'level_middle';
    case LEVEL_HARD = 'level_hard';
    case LEVEL_ANY = 'level_any';
    case REPEAT_FLOW = 'repeat_flow';
    case SEND_TO_CHANNEL = 'send_to_channel';
    case POLLS_CHOSEN = 'polls_chosen';

    public function toState(): string
    {
        return match ($this) {
            self::CREATE_SURVEY => StateEnum::POLL_TYPE_CHOICE->value,
            self::TYPE_QUIZ,
            self::TYPE_SURVEY => StateEnum::POLL_ANONYMITY_CHOICE->value,
        };
    }
}
