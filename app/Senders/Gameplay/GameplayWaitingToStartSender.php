<?php

namespace App\Senders\Gameplay;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameplayWaitingToStartSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameplayWaitingToStart;

    public function send(): void
    {
        $this->addToTrash();

        $game = $this->user->games->last(); // TODO: Change logic ...
        $pollIds = explode(',', $game->poll_ids);
        $countQuestions = count($pollIds);
        $timeLimit = $game->time_limit;

        $text = "🎲 Приготовьтесь пройти игру «{$game->title}»\n\n";
        $text .= "🖊 {$countQuestions} вопроса\n";
        $text .= "⏱️ {$timeLimit} секунд на вопрос";

        $this->sendMessage($text, self::STATE->buttons());
    }
}
