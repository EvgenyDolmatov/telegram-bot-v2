<?php

namespace App\Commands;

use App\Enums\CommandEnum;

class CommandContainer
{
    public static function retrieve(string $commandIdentifier): CommandInterface
    {
        $commands = array_column(CommandEnum::cases(), 'value');
        if (in_array($commandIdentifier, $commands)) {
            return CommandEnum::tryFrom($commandIdentifier)->getCommand();
        }

        return new DefaultCommand();
    }
}
