<?php

namespace App\Senders\Game;

use App\Enums\StateEnum;
use App\Models\Game;
use App\Senders\AbstractSender;
use Exception;

class GameCreatedMenuShowSender extends AbstractSender
{
    private const StateEnum STATE = StateEnum::GameCreatedMenuShow;

    public function send(): void
    {
        $this->addToTrash();

        $game = $this->createGame();
        $questionsQty = count(explode(',', $game->poll_ids));

        $text = "<b>Викторина «{$game->title}» создана!</b>\n\n$questionsQty вопросов, задержка времени: $game->time_limit секунд.";

        $this->sendMessage($text, self::STATE->buttons());
    }

    /**
     * @throws Exception
     */
    private function createGame(): Game
    {
        if (!$openedFlow = $this->user->getOpenedFlow()) {
            throw new Exception('Flow data is empty');
        }

        $flowData = json_decode($openedFlow->flow, true);
        $openedFlow->update(['is_closed' => true]);

        return Game::create([
            'user_id' => $this->user->id,
            'poll_ids' => $flowData[StateEnum::GamePollsChoice->value],
            'title' => $flowData[StateEnum::GameTitleWaiting->value],
            'time_limit' => $this->getTimeLimit($flowData[StateEnum::GameTimeLimitChoice->value]),
        ]);
    }

    private function getTimeLimit(string $timeLimitChoice): int
    {
        $timeLimitArray = explode('_', $timeLimitChoice);
        return (int)end($timeLimitArray);
    }
}
