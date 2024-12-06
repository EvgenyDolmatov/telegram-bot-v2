<?php

namespace App\States\Admin;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class StatisticUsersMenuChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::ADMIN_STATISTIC_USERS_MENU_CHOICE;

    public function handleInput(string $input, UserContext $context): void
    {
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
