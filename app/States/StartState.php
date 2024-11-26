<?php

namespace App\States;

class StartState implements UserState
{
    public function handleCommand(string $command, UserContext $context): void
    {
        switch ($command) {
            case '/start':
                $context->setState(new StartState());

                // Обработка команды "/start" в состоянии start
                break;
            case '/account':
                $context->setState(new AccountState());

                // Обработка команды "/account" в состоянии start
                break;
        }
    }

    public function handleInput(string $input, UserContext $context): void
    {
        // Выберите опцию экрана в состоянии "start"
    }
}
