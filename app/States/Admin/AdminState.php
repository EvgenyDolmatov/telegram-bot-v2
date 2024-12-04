<?php

namespace App\States\Admin;

use App\Enums\StateEnum;
use App\States\AbstractState;

class AdminState extends AbstractState
{
    private const StateEnum STATE = StateEnum::ADMIN;

    public function handleInput(string $input, $context): void
    {
        $this->deletePreparedPoll();
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
