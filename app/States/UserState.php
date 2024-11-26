<?php

namespace App\States;

interface UserState
{
    public function handleCommand(string $command, UserContext $context): void;
    public function handleInput(string $input, UserContext $context): void;
}
