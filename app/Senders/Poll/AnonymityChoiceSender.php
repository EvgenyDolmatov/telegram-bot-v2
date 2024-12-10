<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AnonymityChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::POLL_ANONYMITY_CHOICE;

    public function send(): void
    {
        $this->editMessageCaption(
            messageId: $this->user->tg_message_id,
            text: self::STATE->title(),
            buttons: self::STATE->buttons()
        );
    }
}
