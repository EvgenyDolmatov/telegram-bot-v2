<?php

namespace App\States\Account;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class ReferredUsersShowState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::ACCOUNT_REFERRED_USERS_SHOW;

    public function handleInput(string $input, UserContext $context): void
    {
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
