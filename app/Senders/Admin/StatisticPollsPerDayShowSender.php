<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Models\AiRequest;
use App\Senders\AbstractSender;
use Carbon\Carbon;

class StatisticPollsPerDayShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::ADMIN_STATISTIC_POLLS_PER_DAY_SHOW;

    public function send(): void
    {
        $this->addToTrash();

        $requestsToday = AiRequest::whereDate('created_at', Carbon::today())->get();
        $text = $requestsToday->count() > 0
            ? "Количество созданных тестов за сегодня: {$requestsToday->count()}"
            : self::STATE->title();

        $this->sendMessage($text, self::STATE->buttons());
    }
}
