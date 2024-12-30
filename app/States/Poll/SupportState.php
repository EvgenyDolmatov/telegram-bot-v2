<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class SupportState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::PollSupport;

    public function handleInput(string $input, UserContext $context): void
    {
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
