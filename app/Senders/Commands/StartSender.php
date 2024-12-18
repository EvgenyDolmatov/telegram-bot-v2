<?php

namespace App\Senders\Commands;

use App\Enums\StateEnum;
use App\Exceptions\ResponseException;
use App\Senders\AbstractSender;

class StartSender extends AbstractSender
{
    /**
     * @throws ResponseException
     */
    public function send(): void
    {
        $this->addToTrash();

        $this->sendPhoto(
            imageUrl: asset('assets/img/start.png'),
            text: StateEnum::START->title(),
            buttons: StateEnum::START->buttons()
        );
    }
}
