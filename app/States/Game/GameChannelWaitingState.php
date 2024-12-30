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
    private const StateEnum STATE = StateEnum::GameChannelWaiting;

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
        if ($input === CallbackEnum::Back->value) {
            return $baseState->backState();
        }

        return StateEnum::GameCreatedSuccessShow;
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
            'poll_ids' => $data[StateEnum::GamePollsChoice->value] ?? null,
            'title' => $data[StateEnum::GameTitleWaiting->value] ?? null,
            'description' => $data[StateEnum::GameDescriptionWaiting->value] ?? null,
            'time_limit' => $data[StateEnum::GameTimeLimitWaiting->value] ?? null,
            'channel' => $data[StateEnum::GameChannelWaiting->value] ?? null,
        ]);

        // Close flow
        $flow->update(['is_completed' => true]);

        // Delete prepared poll
        if ($preparedPoll = $this->user->getPreparedPoll()) {
            $preparedPoll->delete();
        }
    }
}
