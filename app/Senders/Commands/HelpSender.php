<?php

namespace App\Senders\Commands;

use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Senders\AbstractSender;

class HelpSender extends AbstractSender
{
    public function process(): void
    {
        $this->addToTrash();

        $text = "Если у вас есть вопросы, напишите мне в личные сообщения: <a href='https://t.me/nkm_studio'>https://t.me/nkm_studio</a>";
        $buttons = [new ButtonDto(CommandEnum::START->value, 'Назад')];

        $message = $this->messageBuilder->createMessage($text, $buttons);
        $this->senderService->sendMessage($message);
    }
}
