<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ChannelNameWaitingSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $text = StateEnum::CHANNEL_NAME_WAITING->title();

        $message = $this->messageBuilder->createMessage($text);
        $this->senderService->sendMessage($message);
    }
}
