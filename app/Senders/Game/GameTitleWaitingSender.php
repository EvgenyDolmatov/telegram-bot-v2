<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameTitleWaitingSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::CHANNEL_NAME_WAITING->title(),
            buttons: StateEnum::CHANNEL_NAME_WAITING->buttons()
        );
    }
}
