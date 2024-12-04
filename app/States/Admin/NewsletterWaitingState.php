<?php

namespace App\States\Admin;

use App\Enums\StateEnum;
use App\States\AbstractState;

class NewsletterWaitingState extends AbstractState
{
    private const StateEnum STATE = StateEnum::ADMIN_NEWSLETTER_WAITING;

    public function handleInput(string $input, $context): void
    {
        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
