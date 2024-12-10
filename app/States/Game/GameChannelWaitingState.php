<?php

namespace App\States\Game;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Game;
use App\Models\UserFlow;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GameChannelWaitingState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GAME_CHANNEL_WAITING;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update flow
        $userFlow = $this->user->updateFlow(self::STATE, $this->getChannelName($input));

        // Create game and close flow
        $this->createGame($userFlow);

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

        return StateEnum::GAME_CREATED_SUCCESS_SHOW;
    }

    private function getChannelName(string $input): string
    {
        if (str_contains($input, 'https://t.me/')) {
            return "@" . substr($input, 13);
        }

        return '@' . ltrim($input, '@');
    }

    private function createGame(UserFlow $flow): void
    {
        $data = json_decode($flow->flow, true);

        Game::create([
            'user_id' => $this->user->id,
            'poll_ids' => $data[StateEnum::GAME_POLLS_CHOICE->value] ?? null,
            'title' => $data[StateEnum::GAME_TITLE_WAITING->value] ?? null,
            'description' => $data[StateEnum::GAME_DESCRIPTION_WAITING->value] ?? null,
            'time_limit' => $data[StateEnum::GAME_TIME_LIMIT_WAITING->value] ?? null,
            'channel' => $data[StateEnum::GAME_CHANNEL_WAITING->value] ?? null,
        ]);

        // Close flow
        $flow->update(['is_completed' => true]);

        // Delete prepared poll
        if ($preparedPoll = $this->user->getPreparedPoll()) {
            $preparedPoll->delete();
        }
    }
}
