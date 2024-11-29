<?php

namespace App\Senders\Commands;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ChannelSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $message = $this->messageBuilder->createMessage(StateEnum::CHANNEL->title());
        $this->senderService->sendMessage($message);
    }
}
