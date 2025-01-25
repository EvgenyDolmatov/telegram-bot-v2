<?php

namespace App\States\Game\Gameplay;

use App\Enums\Callback\GameEnum;
use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Game;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;
use Exception;
use Illuminate\Support\Facades\Log;

class GameplayCountdownShowState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GameplayStart;

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
