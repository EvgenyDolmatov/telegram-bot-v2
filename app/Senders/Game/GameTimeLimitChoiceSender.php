<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameTimeLimitChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameTimeLimitChoice;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
