<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Models\AiRequest;
use App\Senders\AbstractSender;
use Carbon\Carbon;

class StatisticPollsPerWeekShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::AdminStatisticPollsPerWeekShow;

    public function send(): void
    {
        $this->addToTrash();

        $now = Carbon::now();
        $startDate = $now->copy()->modify('-1 week');
        $requestsWeek = AiRequest::whereBetween('created_at', [$startDate, $now])->get();
        $text = $requestsWeek->count() > 0
            ? "Количество созданных тестов за последнюю неделю: {$requestsWeek->count()}"
            : self::STATE->title();

        $this->sendMessage($text, self::STATE->buttons());
    }
}
