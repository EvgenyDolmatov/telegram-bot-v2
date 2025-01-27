<?php

namespace App\Handlers\Message;

use App\Enums\State\GameplayEnum;
use App\Enums\StateEnum;
use App\States\UserContext;

class StateHandler extends AbstractHandler
{
    public function handle(string $message): void
    {
        if (str_starts_with($message, 'gameplay_')) {
            $state = GameplayEnum::from($this->user->getCurrentState());
        } else {
            $state = StateEnum::from($this->user->getCurrentState());
        }

        $userContext = new UserContext($state->userState($this->repository, $this->telegramService));
        $userContext->handleInput($message);
    }
}
