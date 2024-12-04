<?php

namespace App\States\Poll;

use App\Constants\CommonConstants;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class ChannelPollsChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::CHANNEL_POLLS_CHOICE;
    private const string POLL_PREFIX = 'poll_';

    public function handleInput(string $input, UserContext $context): void
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
        if ($input === CommonConstants::BACK) {
            return $baseState->backState();
        }

        if (str_starts_with($input, self::POLL_PREFIX)) {
            return self::STATE;
        }

        return StateEnum::CHANNEL_NAME_WAITING;
    }
}
