<?php

namespace App\States\Admin;

use App\Enums\StateEnum;
use App\States\AbstractState;

class AdminState extends AbstractState
{
    private const StateEnum STATE = StateEnum::Admin;

    public function handleInput(string $input, $context): void
    {
        $this->user->deletePreparedPoll();

        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step
        $this->updateState($state, $context);

        // Send message to chat
        $this->sendMessage($state);
    }
}
