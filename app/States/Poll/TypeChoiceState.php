<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class TypeChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::PollTypeChoice;

    public function handleInput(string $input, UserContext $context): void
    {
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
