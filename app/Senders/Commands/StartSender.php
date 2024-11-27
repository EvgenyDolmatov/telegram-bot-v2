<?php

namespace App\Senders\Commands;

use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\SurveyCallbackEnum;
use App\Senders\AbstractSender;

class StartSender extends AbstractSender
{
    public function process(): void
    {
        $this->addToTrash();

        $state = $this->user->getCurrentState();
        $buttons = $state->prepareButtons($this->user);
        $buttons[] = new ButtonDto(SurveyCallbackEnum::TYPE_QUIZ->value, 'Выбрать тип');

        $message = $this->messageBuilder->createMessage(
            text: $state->text,
            buttons: $buttons
        );

        $this->senderService->sendPhoto(
            message: $message,
            imageUrl: asset('assets/img/start.png')
        );
    }
}
