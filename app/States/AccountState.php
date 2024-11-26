<?php

namespace App\States;

class AccountState extends AbstractState implements UserState
{
    public function handleCommand(string $command, UserContext $context): void
    {
        switch ($command) {
            case '/start':
                $context->setState(new StartState($this->request, $this->telegramService));
                $message = $this->messageSender->createMessage('Hi from Start 200');
                $this->senderService->sendMessage($message);

                // Обработка команды "/start" в состоянии account
                break;
            case '/account':
                $context->setState(new AccountState($this->request, $this->telegramService));
                $message = $this->messageSender->createMessage('Hi from Account 200');
                $this->senderService->sendMessage($message);

                // Обработка команды "/account" в состоянии account
                break;
        }
    }

    public function handleInput(string $input, $context): void
    {
        // Выберите опцию экрана в состоянии "account"
    }
}
