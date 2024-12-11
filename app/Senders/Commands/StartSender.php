<?php

namespace App\Senders\Commands;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class StartSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $message = $this->messageBuilder->createMessage(
            text: StateEnum::START->title(),
            buttons: StateEnum::START->buttons()
        );
        $this->senderService->sendPhoto($message, asset('assets/img/start.png'));
    }
}
