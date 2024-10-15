<?php

namespace App\Commands;

use App\Enums\CommandEnum;

class CommandContainer
{
    public static function retrieve(string $commandIdentifier): CommandInterface
    {
        return CommandEnum::tryFrom($commandIdentifier)->getCommand();
    }
}
