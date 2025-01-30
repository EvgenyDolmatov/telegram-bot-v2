<?php

namespace App\Handlers\Message;

use App\Enums\StateEnum;
use App\States\UserContext;

class StateMessageHandler extends AbstractMessageHandler
{
    public function handle(string $message): void
    {
        $state = StateEnum::from($this->user->getCurrentState());
        $userContext = new UserContext($state->userState($this->repository, $this->telegramService));
        $userContext->handleInput($message);
    }
}
