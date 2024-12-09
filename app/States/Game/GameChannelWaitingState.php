<?php

namespace App\States\Game;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Game;
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

        // Send message to chat
        $this->sendMessage($state);

        // Update flow
        $this->user->updateFlow(self::STATE, $this->getChannelName($input));

        // Create game and close flow
        $this->createGame();

        // Update user step
        $this->updateState($state, $context);
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

    private function createGame(): void
    {
        $userFlow = $this->user->flows->where('is_completed', false)->last();

        if ($userFlow) {
            $flowData = json_decode($userFlow->flow, true);

            Game::create([
                'user_id' => $this->user->id,
                'poll_ids' => $flowData[StateEnum::GAME_POLLS_CHOICE->value] ?? null,
                'title' => $flowData[StateEnum::GAME_TITLE_WAITING->value] ?? null,
                'description' => $flowData[StateEnum::GAME_TITLE_WAITING->value] ?? null,
                'time_limit' => $flowData[StateEnum::GAME_TIME_LIMIT_WAITING->value] ?? null,
                'channel' => $flowData[StateEnum::GAME_CHANNEL_WAITING->value] ?? null,
            ]);

            // Close flow
            $userFlow->update(['is_completed' => true]);

            // Delete prepared poll
            if ($preparedPoll = $this->getLastPreparedPoll()) {
                $preparedPoll->delete();
            }
        }
    }
}
