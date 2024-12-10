<?php

namespace App\Senders\Poll;

use App\Dto\ButtonDto;
use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Sector;
use App\Senders\AbstractSender;

class SectorChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::POLL_SECTOR_CHOICE;

    public function send(): void
    {
        $this->editMessageCaption(
            messageId: $this->user->tg_message_id,
            text: self::STATE->title(),
            buttons: $this->getButtons()
        );
    }

    private function getButtons(): array
    {
        $buttons = array_map(fn($sector) => new ButtonDto($sector['code'], $sector['title']), Sector::all()->toArray());
        $buttons[] = new ButtonDto(CallbackEnum::BACK->value, CallbackEnum::BACK->buttonText());

        return $buttons;
    }
}
