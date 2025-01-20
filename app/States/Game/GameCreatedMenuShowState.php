<?php

namespace App\States\Game;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameCreatedMenuShowState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GameCreatedSuccessShow;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step
        $this->updateState($state, $context);

        // Send message to chat
        $this->sendMessage($state);
    }
}
