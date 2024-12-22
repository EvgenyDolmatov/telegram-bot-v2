<?php

namespace App\States;

use App\Enums\CallbackEnum;
use App\Enums\CommandEnum;
use App\Enums\StateEnum;
use App\Models\User;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\TelegramService;

abstract class AbstractState implements UserState
{
    protected User $user;

    public function __construct(
        protected readonly RepositoryInterface $repository,
        protected readonly TelegramService $telegramService
    ) {
        $this->user = User::getOrCreate($repository);
    }

    public function handleCommand(string $command, UserContext $context): void
    {
        $command = ltrim($command, '/');
        $state = CommandEnum::from($command)->toState();

        $this->user->resetFlow();
        $this->baseHandle($state, $context);
    }

    abstract public function handleInput(string $input, UserContext $context): void;

    protected function handleSimpleInput(string $input, UserContext $context, StateEnum $baseState): void
    {
        // If unexpected callback, staying at current step
        $availableValues = $this->getAvailableCallbackValues($baseState);
        if (!empty($availableValues) && !in_array($input, $availableValues)) {
            $this->sendMessage($baseState);
            return;
        }

        // Move to the next step
        $nextState = $this->getState($input, $baseState);

        $this->user->updateFlow($baseState, $input);
        $this->baseHandle($nextState, $context);
    }

    private function baseHandle(StateEnum $state, UserContext $context): void
    {
        $this->updateState($state, $context);
        $this->sendMessage($state);
    }

    protected function updateState(StateEnum $state, UserContext $context): void
    {
        $context->setState($state->userState($this->repository, $this->telegramService));
        $this->user->updateStateByCode($state->value);
    }

    protected function sendMessage(StateEnum $state): void
    {
        $sender = $state->sender($this->repository, $this->telegramService, $this->user);
        $sender->send();
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        return $input === CallbackEnum::BACK->value
            ? $baseState->backState()
            : CallbackEnum::from($input)->toState();
    }

    protected function getAvailableCallbackValues(StateEnum $baseState): array
    {
        $values = [];
        foreach ($baseState->buttons() as $button) {
            $values[] = $button->getCallbackData();
        }

        return $values;
    }
}
