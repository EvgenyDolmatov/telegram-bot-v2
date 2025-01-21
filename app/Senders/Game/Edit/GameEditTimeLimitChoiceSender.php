<?php

namespace App\Senders\Game\Edit;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameEditTimeLimitChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameTimeLimitChoice;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
