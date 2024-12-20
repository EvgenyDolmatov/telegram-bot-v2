<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameTitleWaitingSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GAME_TITLE_WAITING;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
