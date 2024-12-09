<?php

namespace App\States\Game;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameTitleWaitingState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GAME_TITLE_WAITING;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Send message to chat
        $this->sendMessage($state);

        // Update flow
        $this->user->updateFlow(self::STATE, $input);

        // Update user step
        $this->updateState($state, $context);

        // Update game
//        $this->updateGame('title', $input);
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CallbackEnum::BACK->value) {
            return $baseState->backState();
        }

        return StateEnum::GAME_DESCRIPTION_WAITING;
    }
}
