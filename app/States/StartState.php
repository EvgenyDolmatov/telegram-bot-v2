<?php

namespace App\States;

use App\Enums\StateEnum;

class StartState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::Start;

    public function handleInput(string $input, UserContext $context): void
    {
        $this->user->deletePreparedPoll();
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
