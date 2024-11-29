<?php

namespace App\Senders\Poll;

use App\Dto\ButtonDto;
use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class AiRespondedChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $buttons = [
            new ButtonDto(PollEnum::LEVEL_EASY->value, PollEnum::LEVEL_EASY->buttonText()),
            new ButtonDto(PollEnum::LEVEL_MIDDLE->value, PollEnum::LEVEL_MIDDLE->buttonText()),
            new ButtonDto(PollEnum::LEVEL_HARD->value, PollEnum::LEVEL_HARD->buttonText()),
            new ButtonDto(PollEnum::LEVEL_ANY->value, PollEnum::LEVEL_ANY->buttonText()),
        ];

        $message = $this->messageBuilder->createMessage(StateEnum::POLL_DIFFICULTY_CHOICE->title(), $buttons);
        $this->senderService->sendMessage($message);
    }
}
