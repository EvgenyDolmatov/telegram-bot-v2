<?php

namespace App\Handlers;

use App\Enums\GameplayEnum;
use App\Handlers\Message\AbstractHandler;
use App\States\UserContext;
use Illuminate\Support\Facades\Log;

class PollAnswerHandler extends AbstractHandler
{
    public function handle(string $message): void
    {
        Log::debug("PollAnswerHandler");

        $state = GameplayEnum::from($this->user->getCurrentState());
        $userContext = new UserContext($state->userState($this->repository, $this->telegramService));
        $userContext->handleInput($message);
    }
}
