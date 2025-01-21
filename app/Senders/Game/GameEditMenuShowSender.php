<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameEditMenuShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameEditMenuShow;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
