<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class TypeChoiceState extends AbstractState implements UserState
{
    private const string STATE = StateEnum::POLL_TYPE_CHOICE->value;

    public function handleInput(string $input, UserContext $context): void
    {
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
