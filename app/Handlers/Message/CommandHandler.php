<?php

namespace App\Handlers\Message;

use App\Enums\CommandEnum;
use App\States\UserContext;

class CommandHandler extends AbstractHandler
{
    /**
     * @throws \Exception
     */
    public function handle(string $message): void
    {
        $command = CommandEnum::from($this->user->getCurrentState());
        $userContext = new UserContext($command->userState($this->request, $this->telegramService));
        $userContext->handleCommand($message);
    }
}
