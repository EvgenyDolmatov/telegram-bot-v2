<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameCreatedSuccessShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GAME_CREATED_SUCCESS_SHOW;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
