<?php

namespace App\States\Game\Edit;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameEditTitleWaitingState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GameEditTitleWaiting;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step
        $this->updateState($state, $context);
        $this->updateGame($input);

        // Send message to chat
        $this->sendMessage($state);
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CallbackEnum::Back->value) {
            return $baseState->backState();
        }

        return StateEnum::GameCreatedMenuShow;
    }

    private function updateGame(string $input): void
    {
        if ($game = $this->user->games->last()) {
            $game->update(['title' => $input]);
        }
    }
}
