<?php

namespace App\States\Gameplay;

use App\Enums\State\GameplayEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameplayWaitingToStartState extends AbstractState implements UserState
{
    private const GameplayEnum STATE = GameplayEnum::WaitingToStart;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step
//        $this->updateState($state, $context);

        // Send message to chat
        $this->sendMessage($state);
    }
}
