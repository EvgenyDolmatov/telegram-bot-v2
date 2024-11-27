<?php

namespace App\Senders\Poll;

use App\Models\User;
use App\Senders\AbstractSender;

class TypeChoiceSender extends AbstractSender
{
    public function process(): void
    {
        $this->addToTrash();

        $state = $this->user->getCurrentState();
        $message = $this->messageBuilder->createMessage(
            text: $state->text,
            buttons: $state->prepareButtons($this->user)
        );

        $this->senderService->sendMessage($message);
    }
}
