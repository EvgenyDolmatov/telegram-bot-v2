<?php

namespace App\Senders\Poll;

use App\Dto\Telegram\Message\Component\ButtonDto;
use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Sector;
use App\Senders\AbstractSender;

class SectorChoiceSender extends AbstractSender
{
    public function send(): void
    {
        $this->addToTrash();

        $buttons = array_map(
            fn($sector) => new ButtonDto($sector['code'], $sector['title']),
            Sector::all()->toArray()
        );

        $buttons[] = new ButtonDto(CallbackEnum::Back->value, CallbackEnum::Back->buttonText());

        $this->sendMessage(StateEnum::PollSectorChoice->title(), $buttons);
    }
}
