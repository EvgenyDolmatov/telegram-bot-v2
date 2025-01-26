<?php

namespace App\States\Game;

use App\Enums\Callback\GameEnum;
use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Game;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;
use Exception;
use Illuminate\Support\Str;

class GameTimeLimitWaitingState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GameTimeLimitChoice;
    private const StateEnum NEXT_STATE = StateEnum::GameCreatedMenuShow;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // If user sent unexpected message
        $availableValues = $this->getAvailableCallbackValues(self::STATE);
        if (!empty($availableValues) && !in_array($input, $availableValues)) {
            $this->sendMessage(self::STATE);
            return;
        }

        // Update user step
        $this->user->updateFlow(self::STATE, $input);

        // Update user step
        $this->updateState($state, $context);

        if ($state === self::NEXT_STATE) {
            $this->createGame();
        }

        // Send message to chat
        $this->sendMessage($state);
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CallbackEnum::Back->value) {
            return $baseState->backState();
        }

        return GameEnum::from($input)->toState();
    }

    private function createGame(): Game
    {
        if (!$openedFlow = $this->user->getOpenedFlow()) {
            throw new Exception('Flow data is empty');
        }

        $flowData = json_decode($openedFlow->flow, true);
        $openedFlow->update(['is_completed' => true]);

        return Game::create([
            'code' => Str::random(10),
            'user_id' => $this->user->id,
            'poll_ids' => $flowData[StateEnum::GamePollsChoice->value],
            'title' => $flowData[StateEnum::GameTitleWaiting->value],
            'time_limit' => $this->getTimeLimit($flowData[StateEnum::GameTimeLimitChoice->value]),
        ]);
    }

    private function getTimeLimit(string $timeLimitChoice): int
    {
        $timeLimitArray = explode('_', $timeLimitChoice);
        return (int)end($timeLimitArray);
    }
}
