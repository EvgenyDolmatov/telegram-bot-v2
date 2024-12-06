<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Models\User;
use App\Senders\AbstractSender;
use Carbon\Carbon;

class StatisticUsersPerDayShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::ADMIN_STATISTIC_USERS_PER_DAY_SHOW;

    public function send(): void
    {
        $this->addToTrash();

        $usersToday = User::whereDate('created_at', Carbon::today())->get();
        $text = $usersToday->count() > 0
            ? "Количество зарегистрированных пользователей сегодня: {$usersToday->count()}"
            : self::STATE->title();

        $this->sendMessage($text, self::STATE->buttons());
    }
}
