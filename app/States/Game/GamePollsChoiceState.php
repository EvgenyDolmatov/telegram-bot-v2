<?php

namespace App\States\Game;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GamePollsChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GAME_POLLS_CHOICE;
    private const string POLL_PREFIX = 'poll_';

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update flow
        // TODO: Check if polls is empty
        if ($preparedPoll = $this->user->getPreparedPoll()) {
            $this->user->updateFlow(self::STATE, $preparedPoll->checked_poll_ids);
        }

        // Update user step
        $this->updateState($state, $context);

        // Send message to chat
        $this->sendMessage($state);
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CallbackEnum::BACK->value) {
            return $baseState->backState();
        }

        if (str_starts_with($input, self::POLL_PREFIX)) {
            return self::STATE;
        }

        return StateEnum::GAME_TITLE_WAITING;
    }
}
