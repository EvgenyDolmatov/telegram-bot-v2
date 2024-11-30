<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class AiRespondedChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::POLL_AI_RESPONDED_CHOICE;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = StateEnum::POLL_AI_RESPONDED_CHOICE; // TODO: Next step

        // Update user step and flow
        $this->user->updateFlow(self::STATE, $input);
        $this->updateState($state, $context); // TODO: Next step

        // Send message to chat
        $sender = $state->sender($this->request, $this->telegramService, $this->user);
        $sender->send();
    }
}
