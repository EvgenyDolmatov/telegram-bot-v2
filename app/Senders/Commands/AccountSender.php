<?php

namespace App\Senders\Commands;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AccountSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(StateEnum::Account->title(), StateEnum::Account->buttons());
    }
}
