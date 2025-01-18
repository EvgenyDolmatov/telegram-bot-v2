<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ThemeChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::PollThemeChoice->title(),
            buttons: StateEnum::PollThemeChoice->buttons()
        );
    }
}
