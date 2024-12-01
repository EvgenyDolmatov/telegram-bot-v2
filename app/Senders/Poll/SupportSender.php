<?php

namespace App\Senders\Poll;

use App\Constants\CommonConstants;
use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class SupportSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $buttons = [new ButtonDto(CommonConstants::BACK, "Назад")];

        $message = $this->messageBuilder->createMessage(StateEnum::POLL_SUPPORT->title(), $buttons);
        $this->senderService->sendMessage($message);
    }
}
