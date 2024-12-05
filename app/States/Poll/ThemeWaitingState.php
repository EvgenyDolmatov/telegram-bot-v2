<?php

namespace App\States\Poll;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class ThemeWaitingState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::POLL_THEME_WAITING;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step and flow
        $this->user->updateFlow(self::STATE, $input);
        $this->updateState($state, $context);

        // Send message to chat
        $this->sendMessage($state);
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CallbackEnum::BACK->value) {
            return $baseState->backState();
        }

        return StateEnum::POLL_AI_RESPONDED_CHOICE;
    }
}
