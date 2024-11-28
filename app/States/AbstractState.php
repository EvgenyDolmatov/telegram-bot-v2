<?php

namespace App\States;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Enums\CommandEnum;
use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

abstract class AbstractState implements UserState
{
    protected SenderService $senderService;
    protected MessageSender $messageSender;
    protected User $user;

    public function __construct(
        protected readonly Request $request,
        protected readonly TelegramService $telegramService
    ) {
        $this->senderService = new SenderService($request, $telegramService);
        $this->messageSender = (new MessageSender())->setBuilder(new MessageBuilder());

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
        $state = PollEnum::from($input)->toState();

        $this->user->updateFlow($baseState, $input);
        $this->baseHandle($state, $context);
    }

    protected function updateState(StateEnum $state, UserContext $context): void
    {
        $context->setState($state->userState($this->request, $this->telegramService));
        $this->user->updateStateByCode($state->value);
    }

    private function baseHandle(StateEnum $newSate, UserContext $context): void
    {
        $this->updateState($newSate, $context);

        $sender = $newSate->sender($this->request, $this->messageSender, $this->senderService);
        $sender->process();
    }

    private function clearCommand(string $command): string
    {
        return ltrim($command, '/');
    }
}
