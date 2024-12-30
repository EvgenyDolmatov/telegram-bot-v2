<?php

namespace App\Senders\Commands;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AdminSender extends AbstractSender
{
    public function send(): void
    {
        if (!$this->user->isAdmin()) {
            $this->someProblemMessage();
            return;
        }

        $this->sendMessage(
            text: StateEnum::Admin->title(),
            buttons: StateEnum::Admin->buttons()
        );
    }
}
