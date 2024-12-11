<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class TypeChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::POLL_TYPE_CHOICE->title(),
            buttons: StateEnum::POLL_TYPE_CHOICE->buttons()
        );
    }
}
