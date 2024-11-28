<?php

namespace App\Senders\Commands;

use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class HelpSender extends AbstractSender
{
    public function process(): void
    {
        $this->addToTrash();

        $buttons = [new ButtonDto(CommandEnum::START->value, 'Назад')];

        $message = $this->messageBuilder->createMessage(StateEnum::HELP->title(), $buttons);
        $this->senderService->sendMessage($message);
    }
}
