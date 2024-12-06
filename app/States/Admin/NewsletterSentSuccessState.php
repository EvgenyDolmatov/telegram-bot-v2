<?php

namespace App\States\Admin;

use App\Enums\StateEnum;
use App\States\AbstractState;

class NewsletterSentSuccessState extends AbstractState
{
    private const StateEnum STATE = StateEnum::ADMIN_NEWSLETTER_SENT_SUCCESS;

    public function handleInput(string $input, $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step
        $this->updateState($state, $context);

        // Send message to chat
        $this->sendMessage($state);
    }
}
