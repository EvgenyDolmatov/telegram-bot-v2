<?php

namespace App\Senders\Commands;

use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ChannelSender extends AbstractSender
{
    public function process(): void
    {
        $this->addToTrash();

        $message = $this->messageBuilder->createMessage(StateEnum::CHANNEL->title());
        $this->senderService->sendMessage($message);
    }
}
