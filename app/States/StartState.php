<?php

namespace App\States;


use App\Enums\CommandEnum;
use App\Models\User;

class StartState extends AbstractState implements UserState
{
    public function handleCommand(string $command, UserContext $context): void
    {
        $command = $this->updateState($command, $context);

        switch ($command) {
            case CommandEnum::ACCOUNT->value:
                $message = $this->messageSender->createMessage('From Start to Account');
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::ADMIN->value:
                $message = $this->messageSender->createMessage('From Start to Admin');
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::CHANNEL->value:
                $message = $this->messageSender->createMessage('From Start to Channel');
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::HELP->value:
                $message = $this->messageSender->createMessage('From Start to Help');
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::START->value:
                $message = $this->messageSender->createMessage('From Start to Start');
                $this->senderService->sendMessage($message);

                break;
        }
    }

    public function handleInput(string $input, UserContext $context): void
    {
        // $message = $this->messageSender->createMessage('Hi from Start');
        // $this->senderService->sendMessage($message);
    }
}
