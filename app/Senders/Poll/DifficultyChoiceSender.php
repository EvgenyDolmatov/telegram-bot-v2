<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class DifficultyChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::POLL_DIFFICULTY_CHOICE->title(),
            buttons: StateEnum::POLL_DIFFICULTY_CHOICE->buttons()
        );
    }
}
