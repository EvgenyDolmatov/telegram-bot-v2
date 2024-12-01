<?php

namespace App\States\Poll;

use App\Enums\PollEnum;
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
        $state = $this->getState($input, self::STATE);

        // Update user step and flow
        $this->user->updateFlow(self::STATE, $input, true);
        $this->updateState($state, $context);

        // Send message to chat
        $sender = $state->sender($this->request, $this->telegramService, $this->user);
        $sender->send();
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        return match ($input) {
            PollEnum::REPEAT_FLOW->value => StateEnum::POLL_AI_RESPONDED_CHOICE,
            PollEnum::SEND_TO_CHANNEL->value => StateEnum::CHANNEL_POLLS_CHOICE,
            default => StateEnum::START,
        };
    }
}
