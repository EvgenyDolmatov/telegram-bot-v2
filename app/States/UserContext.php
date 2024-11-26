<?php

namespace App\States;

use Illuminate\Support\Facades\Log;

class UserContext
{
    private UserState $state;

    public function __construct(UserState $state)
    {
        $this->state = $state;
    }

    public function setState(UserState $state): void
    {
        $this->state = $state;
    }

    public function handleCommand(string $command): void
    {
        $this->state->handleCommand($command, $this);
    }

    public function handleInput(string $input): void
    {
        $this->state->handleInput($input, $this);
    }
}
