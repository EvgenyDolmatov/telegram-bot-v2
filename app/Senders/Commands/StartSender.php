<?php

namespace App\Senders\Commands;

use App\Enums\StateEnum;
use App\Repositories\MessageRepository;
use App\Senders\AbstractSender;

class StartSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::START;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendPhoto(
            imageUrl: asset('assets/img/start.png'),
        );

        $response = $this->sendMessage(
            text: self::STATE->title(),
            buttons: self::STATE->buttons()
        );

        $messageDto = (new MessageRepository($response))->getDto();
        $this->user->update(['tg_message_id' => $messageDto->getId()]);
    }
}
