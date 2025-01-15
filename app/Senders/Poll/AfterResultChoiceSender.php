<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AfterResultChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        if ($pollGroup = $this->user->pollGroups->where('is_closed', false)->first()) {
            $pollGroup->update(['is_closed' => true]);
        }

        $this->sendMessage(
            text: StateEnum::PollAfterResultChoice->title(),
            buttons: StateEnum::PollAfterResultChoice->buttons()
        );
    }
}
