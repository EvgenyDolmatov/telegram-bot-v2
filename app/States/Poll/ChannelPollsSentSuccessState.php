<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class ChannelPollsSentSuccessState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::CHANNEL_POLLS_SENT_SUCCESS;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = self::STATE;

        // Update user step
        $this->updateState($state, $context);

        // Send message to chat
        $this->sendMessage($state);
    }
}
