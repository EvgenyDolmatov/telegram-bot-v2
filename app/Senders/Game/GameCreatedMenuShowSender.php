<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;
use Exception;

class GameCreatedMenuShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameCreatedMenuShow;

    /**
     * @throws Exception
     */
    public function send(): void
    {
        $this->addToTrash();

        $game = $this->user->games->last();
        $questionsQty = count(explode(',', $game->poll_ids));

        $text = "<b>Викторина «{$game->title}» создана!</b>\n\n{$this->getQuestionsQty($questionsQty)}," .
                " задержка времени: {$this->timeLimitToText($game->time_limit)}.";
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

    private function getQuestionsQty(int $qty): string
    {
        if ($qty % 10 == 1 && $qty % 100 != 11) {
            return "$qty вопрос";
        } elseif ($qty % 10 >= 2 && $qty % 10 <= 4 && ($qty % 100 < 10 || $qty % 100 >= 20)) {
            return "$qty вопроса";
        } else {
            return "$qty вопросов";
        }
    }
}
