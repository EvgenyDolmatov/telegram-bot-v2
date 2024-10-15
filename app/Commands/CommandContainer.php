<?php

namespace App\Commands;

use App\Enums\CommandEnum;
use App\Enums\CommonCallbackEnum;

class CommandContainer
{
    public static function retrieve(string $commandIdentifier): CommandInterface
    {
        $commands = array_column(CommonCallbackEnum::cases(), 'value');
        if (in_array($commandIdentifier, $commands)) {
            return CommandEnum::tryFrom($commandIdentifier)->getCommand();
        }

        return new DefaultCommand();
    }
}
