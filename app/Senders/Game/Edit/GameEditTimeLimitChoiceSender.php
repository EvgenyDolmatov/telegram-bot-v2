<?php

namespace App\Senders\Game\Edit;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameEditTimeLimitChoiceSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameEditTimeLimitChoice;

    public function send(): void
    {
        $this->addToTrash();

        $game = $this->user->games->last();
        $text = "<b>Укажите время для ответа пользователей</b>\n\n" .
                "Текущее значение: «{$this->timeLimitToText($game->time_limit)}»";
        $this->sendMessage($text, self::STATE->buttons());
    }

    private function timeLimitToText(int $timeLimit): string
    {
        return match ($timeLimit) {
            15 => "15 секунд",
            20 => "20 секунд",
            25 => "25 секунд",
            30 => "30 секунд",
            45 => "45 секунд",
            60 => "1 минута",
            180 => "3 минуты",
            300 => "5 минут",
            600 => "10 минут",
            default => 'не известно'
        };
    }
}
