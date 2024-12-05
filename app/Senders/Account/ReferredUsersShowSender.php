<?php

namespace App\Senders\Account;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ReferredUsersShowSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        // ... code ...
    }
}
