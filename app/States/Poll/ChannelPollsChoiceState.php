<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\Models\Subject;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class ChannelPollsChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::CHANNEL_POLLS_CHOICE;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input);

        // Update user step
        $this->updateState($state, $context);

        // Send message to chat
        $sender = $state->sender($this->request, $this->telegramService, $this->user);
        $sender->send();
    }

    private function getState(string $input): StateEnum
    {
        return StateEnum::CHANNEL_NAME_WAITING;
    }
}
