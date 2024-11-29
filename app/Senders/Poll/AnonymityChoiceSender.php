<?php

namespace App\Senders\Poll;

use App\Dto\ButtonDto;
use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AnonymityChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $buttons = [
            new ButtonDto(PollEnum::IS_ANON->value, PollEnum::IS_ANON->buttonText()),
            new ButtonDto(PollEnum::IS_NOT_ANON->value, PollEnum::IS_NOT_ANON->buttonText()),
        ];

        $message = $this->messageBuilder->createMessage(StateEnum::POLL_ANONYMITY_CHOICE->title(), $buttons);
        $this->senderService->sendMessage($message);
    }
}
