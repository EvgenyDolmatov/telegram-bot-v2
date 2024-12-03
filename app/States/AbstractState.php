<?php

namespace App\States;

use App\Constants\CommonConstants;
use App\Enums\CommandEnum;
use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\TelegramService;
use Illuminate\Http\Request;

abstract class AbstractState implements UserState
{
    protected User $user;

    public function __construct(
        protected readonly Request $request,
        protected readonly TelegramService $telegramService
    ) {
        $this->user = User::getOrCreate(new RequestRepository($this->request));
    }

    public function handleCommand(string $command, UserContext $context): void
    {
        $command = $this->clearCommand($command);
        $state = CommandEnum::from($command)->toState();

        $this->user->resetFlow();
        $this->baseHandle($state, $context);
    }

    abstract public function handleInput(string $input, UserContext $context): void;

    protected function handleSimpleInput(string $input, UserContext $context, StateEnum $baseState): void
    {
        $nextState = $this->getState($input, $baseState);

        $this->user->updateFlow($baseState, $input);
        $this->baseHandle($nextState, $context);
    }

    protected function handleRepeatSimpleInput(UserContext $context, StateEnum $baseState): void
    {
        $this->baseHandle($baseState, $context);
    }

    protected function updateState(StateEnum $state, UserContext $context): void
    {
        $context->setState($state->userState($this->request, $this->telegramService));
        $this->user->updateStateByCode($state->value);
    }

    private function baseHandle(StateEnum $state, UserContext $context): void
    {
        $this->updateState($state, $context);

        $sender = $state->sender($this->request, $this->telegramService, $this->user);
        $sender->send();
    }

    private function clearCommand(string $command): string
    {
        return ltrim($command, '/');
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        return $input === CommonConstants::BACK
            ? $baseState->backState()
            : PollEnum::from($input)->toState();
    }

    protected function deletePreparedPoll(): void
    {
        if ($preparedPoll = $this->user->preparedPolls()->first()) {
            $preparedPoll->delete();
        }
    }
}
