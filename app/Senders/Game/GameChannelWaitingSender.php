<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameChannelWaitingSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GAME_CHANNEL_WAITING;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
