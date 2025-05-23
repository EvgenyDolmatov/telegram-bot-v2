<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ThemeWaitingSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::POLL_THEME_WAITING->title(),
            buttons: StateEnum::POLL_THEME_WAITING->buttons()
        );
    }
}
