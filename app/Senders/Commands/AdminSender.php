<?php

namespace App\Senders\Commands;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AdminSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        if (!$this->user->is_admin) {
            $this->someProblemMessage();
            return;
        }

        $this->sendMessage(
            text: StateEnum::ADMIN->title(),
            buttons: StateEnum::ADMIN->buttons()
        );
    }
}
