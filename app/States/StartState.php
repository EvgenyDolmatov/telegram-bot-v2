<?php

namespace App\States;

class StartState extends AbstractState
{
    public function handleInput(string $input, UserContext $context): void
    {
        // $message = $this->messageSender->createMessage('Hi from Start');
        // $this->senderService->sendMessage($message);
    }
}
