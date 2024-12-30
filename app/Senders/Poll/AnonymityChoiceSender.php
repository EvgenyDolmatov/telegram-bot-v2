<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AnonymityChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::PollAnonymityChoice->title(),
            buttons: StateEnum::PollAnonymityChoice->buttons()
        );
    }
}
