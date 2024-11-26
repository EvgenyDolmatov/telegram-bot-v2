<?php

namespace App\States;


use App\Models\User;

class StartState extends AbstractState implements UserState
{
    public function handleCommand(string $command, UserContext $context): void
    {
        switch ($command) {
            case '/start':
                $context->setState(new StartState($this->request, $this->telegramService));
                $message = $this->messageSender->createMessage('Hi from Start');
                $this->senderService->sendMessage($message);

                $user = User::find(4);
                $user->states()->detach();
                $user->states()->attach(1);

                // Обработка команды "/start" в состоянии start
                break;
            case '/account':
                $context->setState(new AccountState($this->request, $this->telegramService));
                $message = $this->messageSender->createMessage('Hi from Account');
                $this->senderService->sendMessage($message);

                $user = User::find(4);
                $user->states()->detach();
                $user->states()->attach(10);

                // Обработка команды "/account" в состоянии start
                break;
        }
    }

    public function handleInput(string $input, UserContext $context): void
    {
        // $message = $this->messageSender->createMessage('Hi from Start');
        // $this->senderService->sendMessage($message);
    }
}
