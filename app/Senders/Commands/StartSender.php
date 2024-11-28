<?php

namespace App\Senders\Commands;

use App\Dto\ButtonDto;
use App\Enums\CommonCallbackEnum;
use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class StartSender extends AbstractSender
{
    public function process(): void
    {
        $this->addToTrash();

        $buttons = [
            new ButtonDto(PollEnum::CREATE_SURVEY->value, PollEnum::CREATE_SURVEY->buttonText()),
            new ButtonDto(CommonCallbackEnum::SUPPORT->value, 'Поддержка'),
        ];

        $message = $this->messageBuilder->createMessage(StateEnum::START->title(), $buttons);
        $this->senderService->sendPhoto($message, asset('assets/img/start.png'));
    }
}
