<?php

namespace App\States\Admin;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;

class NewsletterWaitingState extends AbstractState
{
    private const StateEnum STATE = StateEnum::ADMIN_NEWSLETTER_WAITING;

    public function handleInput(string $input, $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step
        $this->updateState($state, $context);

        // Send message to chat
        $this->sendMessage($state);
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CallbackEnum::Back->value) {
            return $baseState->backState();
        }

        return StateEnum::ADMIN_NEWSLETTER_CONFIRMATION;
    }
}
