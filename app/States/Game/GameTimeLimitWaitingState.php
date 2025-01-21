<?php

namespace App\States\Game;

use App\Enums\StateEnum;
use App\Models\Game;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;
use Exception;

class GameTimeLimitWaitingState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GameTimeLimitChoice;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step
        $this->user->updateFlow(self::STATE, $input);

        // Update user step
        $this->updateState($state, $context);
        $this->createGame();

        // Send message to chat
        $this->sendMessage($state);
    }

    private function createGame(): Game
    {
        if (!$openedFlow = $this->user->getOpenedFlow()) {
            throw new Exception('Flow data is empty');
        }

        $flowData = json_decode($openedFlow->flow, true);
        $openedFlow->update(['is_completed' => true]);

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
