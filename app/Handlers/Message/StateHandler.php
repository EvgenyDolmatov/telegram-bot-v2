<?php

namespace App\Handlers\Message;

use App\Enums\StateEnum;
use App\States\UserContext;

class StateHandler extends AbstractHandler
{
    public function handle(string $message): void
    {
        $state = StateEnum::from($this->user->getCurrentState()->code);
        $userContext = new UserContext($state->userState($this->repository, $this->telegramService));
        $userContext->handleInput($message);
    }
}
