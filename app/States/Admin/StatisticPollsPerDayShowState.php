<?php

namespace App\States\Admin;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class StatisticPollsPerDayShowState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::ADMIN_STATISTIC_POLLS_PER_DAY_SHOW;

    public function handleInput(string $input, UserContext $context): void
    {
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
