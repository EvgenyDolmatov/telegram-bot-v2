<?php

namespace App\Senders\Commands;

use App\Senders\AbstractSender;

class StartSender extends AbstractSender
{
    public function process(): void
    {
        $this->addToTrash();

        $state = $this->user->getCurrentState();
        $message = $this->messageBuilder->createMessage(
            text: $state->text,
            buttons: $state->prepareButtons($this->user)
        );

        $this->senderService->sendPhoto(
            message: $message,
            imageUrl: asset('assets/img/start.png')
        );
    }
}
