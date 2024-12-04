<?php

namespace App\Senders\Poll;

use App\Constants\CommonConstants;
use App\Dto\ButtonDto;
use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class ThemeWaitingSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(
            text: StateEnum::POLL_THEME_WAITING->title(),
            buttons: [new ButtonDto(CommonConstants::BACK, "Назад")]
        );
    }
}
