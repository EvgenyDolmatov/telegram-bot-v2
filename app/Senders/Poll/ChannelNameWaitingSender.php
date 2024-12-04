<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ChannelNameWaitingSender extends AbstractSender
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
