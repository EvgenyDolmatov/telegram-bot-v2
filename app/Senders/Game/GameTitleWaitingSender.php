<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameTitleWaitingSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameTitleWaiting;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
