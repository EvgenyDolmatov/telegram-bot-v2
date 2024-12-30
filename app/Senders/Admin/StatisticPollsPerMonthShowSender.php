<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Models\AiRequest;
use App\Senders\AbstractSender;
use Carbon\Carbon;

class StatisticPollsPerMonthShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::AdminStatisticPollsPerMonthShow;

    public function send(): void
    {
        $this->addToTrash();

        $now = Carbon::now();
        $startDate = $now->copy()->modify('-1 month');
        $requestsMonth = AiRequest::whereBetween('created_at', [$startDate, $now])->get();
        $text = $requestsMonth->count() > 0
            ? "Количество созданных тестов за последний месяц: {$requestsMonth->count()}"
            : self::STATE->title();


        $this->sendMessage($text, self::STATE->buttons());
    }
}
