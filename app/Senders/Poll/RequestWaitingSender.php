<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class RequestWaitingSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::PollRequestWaiting->title(),
            buttons: StateEnum::PollRequestWaiting->buttons()
        );
    }
}
