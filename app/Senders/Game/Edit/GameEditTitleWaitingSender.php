<?php

namespace App\Senders\Game\Edit;

use App\Enums\StateEnum;
use App\Senders\AbstractSender;

class GameEditTitleWaitingSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameEditTitleWaiting;

    public function send(): void
    {
        $this->addToTrash();

        $game = $this->user->games->last();
        $text = "<b>Введите название викторины</b>\n\nТекущее название викторины: «{$game->title}»";

        $this->sendMessage($text, self::STATE->buttons());
    }
}
