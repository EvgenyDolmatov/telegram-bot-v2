<?php

namespace App\States\Game\Edit;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameEditPollsChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GameEditPollsChoice;
    private const string POLL_PREFIX = 'poll_';

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

        if (str_starts_with($input, self::POLL_PREFIX)) {
            return self::STATE;
        }

        return StateEnum::GameCreatedMenuShow;
    }

    private function updateGame(string $input): void
    {
        if ($game = $this->user->games->last()) {
            $pollIds = explode(',', $game->poll_ids);
            [,$chosenId] = explode('_', $input);

            if (is_numeric($chosenId)) {
                if (!in_array($chosenId, $pollIds)) {
                    $pollIds[] = $chosenId;
                } else {
                    $pollIds = array_diff($pollIds, [$chosenId]);
                }
            }

            $game->update(['poll_ids' => implode(',', $pollIds)]);
        }
    }
}
