<?php

namespace App\Senders\Commands;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class HelpSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::Help->title(),
            buttons: StateEnum::Help->buttons()
        );
    }
}
