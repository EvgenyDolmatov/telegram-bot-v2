<?php

namespace App\States\Game;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameCreatedSuccessState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GAME_CHANNEL_WAITING;

    public function handleInput(string $input, UserContext $context): void
    {
        // ... code ...
    }
}
