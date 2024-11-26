<?php

namespace App\States;

class AccountState implements UserState
{
    public function handleCommand(string $command, UserContext $context): void
    {
        switch ($command) {
            case '/start':
                $context->setState(new StartState());

                // Обработка команды "/start" в состоянии account
                break;
            case '/account':
                $context->setState(new AccountState());

                // Обработка команды "/account" в состоянии account
                break;
        }
    }

    public function handleInput(string $input, $context): void
    {
        // Выберите опцию экрана в состоянии "account"
    }
}
