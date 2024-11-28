<?php

namespace App\Senders\Poll;

use App\Dto\ButtonDto;
use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class TypeChoiceSender extends AbstractSender
{
    public function process(): void
    {
        $this->addToTrash();

        $buttons = [
            new ButtonDto(PollEnum::TYPE_QUIZ->value, PollEnum::TYPE_QUIZ->buttonText()),
            new ButtonDto(PollEnum::TYPE_SURVEY->value, PollEnum::TYPE_SURVEY->buttonText()),
        ];

        $message = $this->messageBuilder->createMessage(StateEnum::POLL_TYPE_CHOICE->title(), $buttons);
        $this->senderService->sendMessage($message);
    }
}
