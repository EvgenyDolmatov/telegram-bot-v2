<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class SupportSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $message = $this->messageBuilder->createMessage(
            text: StateEnum::POLL_SUPPORT->title(),
            buttons: StateEnum::POLL_SUPPORT->buttons()
        );
        $this->senderService->sendMessage($message);
    }
}
