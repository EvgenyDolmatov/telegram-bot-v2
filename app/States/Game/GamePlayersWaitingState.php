<?php

namespace App\States\Game;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class GamePlayersWaitingState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GamePlayersWaiting;
    private const array COMMUNITIES = ['supergroup', 'channel'];

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
        if (!$this->isCallbackQuery() || $this->isCommunityMessage()) {
            return self::STATE;
        }

        if ($input === CallbackEnum::Back->value) {
            return $baseState->backState();
        }

        return StateEnum::GameQuizProcess;
    }

    private function isCallbackQuery(): bool
    {
        return method_exists($this->repository->createDto(), 'getMessage');
    }

    private function isCommunityMessage(): bool
    {
        $dto = $this->repository->createDto();
        $chatType = $dto->getMessage()->getChat()->getType();

        return in_array($chatType, self::COMMUNITIES);
    }
}
