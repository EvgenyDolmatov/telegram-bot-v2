<?php

namespace App\Senders\Poll;

use App\Constants\CommonConstants;
use App\Dto\ButtonDto;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ChannelNameWaitingSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $text = StateEnum::CHANNEL_NAME_WAITING->title();
        $buttons = [new ButtonDto(CommonConstants::BACK, "Назад")];

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }
}
