<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class StatisticPollsMenuChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::ADMIN_STATISTIC_POLLS_MENU_CHOICE;

    public function send(): void
    {
        $this->addToTrash();

        $this->sendMessage(self::STATE->title(), self::STATE->buttons());
    }
}
