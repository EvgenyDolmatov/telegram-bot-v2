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
            text: StateEnum::PollSupport->title(),
            buttons: StateEnum::PollSupport->buttons()
        );
    }
}
