<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameTimeLimitWaitingSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GAME_TIME_LIMIT_WAITING;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
