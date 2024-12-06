<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Models\User;
use App\Senders\AbstractSender;

class StatisticUsersMenuChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::ADMIN_STATISTIC_USERS_MENU_CHOICE;

    public function send(): void
    {
        $this->addToTrash();

        $usersCount = User::all()->count();
        $text = self::STATE->title() . "\n\nОбщее количество пользователей: {$usersCount}";

        $this->sendMessage($text, self::STATE->buttons());
    }
}
