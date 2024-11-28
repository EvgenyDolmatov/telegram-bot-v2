<?php

namespace App\States;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Dto\ButtonDto;
use App\Enums\CommandEnum;
use App\Enums\CommonCallbackEnum;
use App\Enums\StateEnum;
use App\Models\State;
use App\Models\TrashMessage;
use App\Models\User;
use App\Repositories\RequestRepository;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $this->updateState($command, $context);

        $commandItem = StateEnum::from($command);
        $sender = $commandItem->sender($this->request, $this->messageSender, $this->senderService);

        $sender->process();
    }

    abstract public function handleInput(string $input, UserContext $context): void;

    protected function updateState(string $state, UserContext $context): void
    {
        $newState = StateEnum::from($state);

        $context->setState($newState->userState($this->request, $this->telegramService));
//        $this->user->updateStateByCode($state);
    }

    private function clearCommand(string $command): string
    {
        return ltrim($command, '/');
    }
}
