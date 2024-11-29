<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ThemeWaitingSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $message = $this->messageBuilder->createMessage(StateEnum::POLL_THEME_WAITING->title());
        $this->senderService->sendMessage($message);
    }
}
