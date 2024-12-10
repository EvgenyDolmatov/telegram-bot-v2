<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class TypeChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::POLL_TYPE_CHOICE;

    public function send(): void
    {
        $this->addToTrash();

        $this->editMessageCaption(
            messageId: $this->user->tg_message_id,
            text: self::STATE->title(),
            buttons: self::STATE->buttons()
        );
    }
}
