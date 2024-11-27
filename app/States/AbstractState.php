<?php

namespace App\States;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Enums\CommandEnum;
use App\Models\State;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

abstract class AbstractState implements UserState
{
    protected SenderService $senderService;
    protected MessageSender $messageSender;

    public function __construct(
        protected readonly Request $request,
        protected readonly TelegramService $telegramService
    ) {
        $this->senderService = new SenderService($request, $telegramService);
        $this->messageSender = (new MessageSender())->setBuilder(new MessageBuilder());
    }

    abstract public function handleInput(string $input, UserContext $context): void;

    protected function updateState(string $command, UserContext $context): string
    {
        $command = $this->clearCommand($command);
        $newState = CommandEnum::from($command);

        $context->setState($newState->userState($this->request, $this->telegramService));

        $user = User::getOrCreate(new RequestRepository($this->request));
        $currentState = State::where('code', $command)->first();
        $user->states()->detach();
        $user->states()->attach($currentState->id);

        return $command;
    }

    protected function clearCommand(string $command): string
    {
        return ltrim($command, '/');
    }
}
