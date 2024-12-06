<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Models\AiRequest;
use App\Senders\AbstractSender;
use Carbon\Carbon;

class StatisticPollsPerYearShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::ADMIN_STATISTIC_POLLS_PER_YEAR_SHOW;

    public function send(): void
    {
        $this->addToTrash();

        $now = Carbon::now();
        $startDate = $now->copy()->modify('-1 year');
        $requestsYear = AiRequest::whereBetween('created_at', [$startDate, $now])->get();
        $text = $requestsYear->count() > 0
            ? "Количество созданных тестов за последний год: {$requestsYear->count()}"
            : self::STATE->title();

        $this->sendMessage($text, self::STATE->buttons());
    }
}
