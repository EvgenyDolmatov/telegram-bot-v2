<?php

namespace App\States;

class ExpectingTextState implements UserState
{
    public function handleCommand(string $command, UserContext $context): void
    {
        // В этом состоянии я ожидаю текстовое сообщение от вас,
        // Команды обрабатываться не будут
    }

    public function handleInput(string $input, $context): void
    {
        // Обработка произвольного текста
    }
}
