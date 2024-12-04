<?php

namespace App\Senders\Commands;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AdminSender extends AbstractSender
{
    public function send(): void
    {
        if ($this->user->is_admin) {
            $this->sendMessage(
                text: StateEnum::ADMIN->title(),
                buttons: StateEnum::ADMIN->buttons()
            );
            return;
        }

        $this->someProblemMessage();
    }
}
