<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AnonymityChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $message = $this->messageBuilder->createMessage(
            text: StateEnum::POLL_ANONYMITY_CHOICE->title(),
            buttons: StateEnum::POLL_ANONYMITY_CHOICE->buttons()
        );
        $this->senderService->sendMessage($message);
    }
}
