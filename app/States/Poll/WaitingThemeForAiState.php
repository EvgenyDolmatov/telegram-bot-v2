<?php

namespace App\States\Poll;

use App\States\UserContext;
use App\States\UserState;

class WaitingThemeForAiState implements UserState
{
    public function handleCommand(string $command, UserContext $context): void
    {
        // Пожалуйста, просто введите тему для Open AI
    }

    public function handleInput(string $input, UserContext $context): void
    {
        // Логика обработки темы для OpenAI и вывод ответа от AI
        // $context->setState(new NewState());
    }
}
