<?php

namespace App\Senders\Admin;

use App\Enums\StateEnum;
use App\Models\AiRequest;
use App\Senders\AbstractSender;
use Carbon\Carbon;

class StatisticPollsPerQuarterShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::ADMIN_STATISTIC_POLLS_PER_QUARTER_SHOW;

    public function send(): void
    {
        $this->addToTrash();

        $now = Carbon::now();
        $startDate = $now->copy()->modify('-3 month');
        $requestsQuarter = AiRequest::whereBetween('created_at', [$startDate, $now])->get();
        $text = $requestsQuarter->count() > 0
            ? "Количество созданных тестов за последний квартал: {$requestsQuarter->count()}"
            : self::STATE->title();

        $this->sendMessage($text, self::STATE->buttons());
    }
}
