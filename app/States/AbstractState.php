<?php

namespace App\States;

use App\Enums\CommandEnum;
use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Game;
use App\Models\PreparedPoll;
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
        $context->setState($state->userState($this->request, $this->telegramService));
        $this->user->updateStateByCode($state->value);
    }

    protected function sendMessage(StateEnum $state): void
    {
        $sender = $state->sender($this->request, $this->telegramService, $this->user);
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

    protected function getLastPreparedPoll(): ?PreparedPoll
    {
        return $this->user->preparedPolls->last();
    }

    protected function deletePreparedPoll(): void
    {
        if ($preparedPoll = $this->getLastPreparedPoll()) {
            $preparedPoll->delete();
        }
    }

    protected function createGame(): void
    {
        if ($preparedPoll = $this->getLastPreparedPoll()) {
            if ($lastGame = $this->getCurrentGame()) {
                $lastGame->update(['poll_ids' => $preparedPoll->checked_poll_ids ?? null]);
                return;
            }

            Game::create([
                'user_id' => $this->user->id,
                'poll_ids' => $preparedPoll->checked_poll_ids ?? null
            ]);
        }
    }

    protected function updateGame(string $field, string $value): void
    {
        if ($game = $this->user->games->last()) {
            $game->update([$field => $value]);
        }
    }

    private function getCurrentGame(): ?Game
    {
        return $this->user->games()->where('is_completed', false)->latest()->first();
    }

    private function clearCommand(string $command): string
    {
        return ltrim($command, '/');
    }
}
