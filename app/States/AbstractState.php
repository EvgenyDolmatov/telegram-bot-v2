<?php

namespace App\States;

use App\Enums\Callback\GameEnum;
use App\Enums\Callback\GameplayEnum;
use App\Enums\Callback\PollEnum;
use App\Enums\CallbackEnum;
use App\Enums\CommandEnum;
use App\Enums\StateEnum;
use App\Enums\StateInterface;
use App\Enums\ThemeEnum;
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

        if (ThemeEnum::tryFrom($command)) {
            $this->handleInput($command, $context);
            return;
        }

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
        $this->user->update(['state' => $state->value]);
    }

    protected function sendMessage(StateInterface $state): void
    {
        $sender = $state->sender($this->repository, $this->telegramService, $this->user);
        $sender->send();
    }

    protected function getState(string $input, StateInterface $baseState): StateInterface
    {
        if ($input === CallbackEnum::Back->value) {
            return $baseState->backState();
        }

        if (str_starts_with($input, 'poll_')) {
            return PollEnum::from($input)->toState();
        }

        if (str_starts_with($input, 'game_')) {
            return GameEnum::from($input)->toState();
        }

        if (str_starts_with($input, 'gameplay_')) {
            return GameplayEnum::from($input)->toState();
        }

        return CallbackEnum::from($input)->toState();
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
