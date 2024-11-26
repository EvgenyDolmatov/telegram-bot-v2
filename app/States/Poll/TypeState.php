<?php

namespace App\States\Poll;

use App\States\UserContext;
use App\States\UserState;

class TypeState implements UserState
{
    public function handleCommand(string $command, UserContext $context): void
    {
        // ... code ...
    }

    public function handleInput(string $input, UserContext $context): void
    {
        // ... code ...
    }
}
