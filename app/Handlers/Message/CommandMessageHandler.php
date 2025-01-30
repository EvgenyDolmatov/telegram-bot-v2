<?php

namespace App\Handlers\Message;

use App\Enums\StateEnum;
use App\States\UserContext;

class CommandMessageHandler extends AbstractMessageHandler
{
    public function handle(string $message): void
    {
        $state = StateEnum::from($this->user->getCurrentState());
        $userContext = new UserContext($state->userState($this->repository, $this->telegramService));
        $userContext->handleCommand($message);
    }
}
