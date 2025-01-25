<?php

namespace App\Senders\Game\Gameplay;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameplayCountdownShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameplayCountdownShow;

    public function send(): void
    {
        $this->addToTrash();

        $text = "3️⃣ ...";
        $this->sendMessage($text, self::STATE->buttons());
    }
}
