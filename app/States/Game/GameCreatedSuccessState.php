<?php

namespace App\States\Game;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Game;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameCreatedSuccessState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GAME_CREATED_SUCCESS_SHOW;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Send message to chat
        $this->sendMessage($state);

        // Update user step
        $this->updateState($state, $context);
    }
}
