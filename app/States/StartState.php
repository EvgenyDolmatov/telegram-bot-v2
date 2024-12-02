<?php

namespace App\States;

use App\Enums\StateEnum;

class StartState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::START;

    public function handleInput(string $input, UserContext $context): void
    {
        $this->deletePreparedPoll();
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
