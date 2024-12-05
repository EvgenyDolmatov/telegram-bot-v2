<?php

namespace App\Senders\Poll;

use App\Dto\ButtonDto;
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

        $buttons[] = new ButtonDto(CallbackEnum::BACK->value, CallbackEnum::BACK->buttonText());

        $this->sendMessage(StateEnum::POLL_SECTOR_CHOICE->title(), $buttons);
    }
}
