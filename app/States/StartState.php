<?php

namespace App\States;

use App\Enums\StateEnum;

class StartState extends AbstractState implements UserState
{
    private const string STATE = StateEnum::START->value;

    public function handleInput(string $input, UserContext $context): void
    {
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
