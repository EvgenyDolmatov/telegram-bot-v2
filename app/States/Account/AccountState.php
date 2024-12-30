<?php

namespace App\States\Account;

use App\Enums\StateEnum;
use App\States\AbstractState;

class AccountState extends AbstractState
{
    private const StateEnum STATE = StateEnum::Account;

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
