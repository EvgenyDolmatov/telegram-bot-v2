<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameCreatedMenuShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameCreatedSuccessShow;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
