<?php

namespace App\States\Admin;

use App\Enums\CommandEnum;
use App\States\AbstractState;
use App\States\UserContext;

class AdminState extends AbstractState
{
    public function handleCommand(string $command, UserContext $context): void
    {
        $command = $this->updateState($command, $context);

        switch ($command) {
            case CommandEnum::ACCOUNT->value:
                $message = $this->messageSender->createMessage('From Admin to Account');
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::ADMIN->value:
                $message = $this->messageSender->createMessage('From Admin to Admin');
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::CHANNEL->value:
                $message = $this->messageSender->createMessage('From Admin to Channel');
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::HELP->value:
                $message = $this->messageSender->createMessage('From Admin to Help');
                $this->senderService->sendMessage($message);

                break;
            case CommandEnum::START->value:
                $message = $this->messageSender->createMessage('From Admin to Start');
                $this->senderService->sendMessage($message);

                break;
        }
    }

    public function handleInput(string $input, $context): void
    {
        // Выберите опцию экрана в состоянии "account"
    }
}
