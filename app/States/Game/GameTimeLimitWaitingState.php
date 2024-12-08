<?php

namespace App\States\Game;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameTimeLimitWaitingState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GAME_TIME_LIMIT_WAITING;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step
        $this->updateState($state, $context);

        // Update game
        // TODO: Check if user sent integer value!!!
        $this->updateGame('time_limit', $input);

        // Send message to chat
        $this->sendMessage($state);
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CallbackEnum::BACK->value) {
            return $baseState->backState();
        }

        return StateEnum::GAME_CHANNEL_WAITING;
    }
}
