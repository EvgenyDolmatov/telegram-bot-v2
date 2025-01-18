<?php

namespace App\Senders\Poll;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AfterAiRespondedChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        if ($pollGroup = $this->user->pollGroups->where('is_closed', false)->first()) {
            $pollGroup->update(['is_closed' => true]);
        }

        $this->sendMessage(
            text: StateEnum::PollAfterAiRespondedChoice->title(),
            buttons: StateEnum::PollAfterAiRespondedChoice->buttons()
        );
    }
}
