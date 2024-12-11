<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class SupportSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::POLL_SUPPORT->title(),
            buttons: StateEnum::POLL_SUPPORT->buttons()
        );
    }
}
