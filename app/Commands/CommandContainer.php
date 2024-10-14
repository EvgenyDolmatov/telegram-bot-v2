<?php

namespace App\Commands;

use App\Enums\CommandEnum;

class CommandContainer
{
    public static function retrieve(string $commandIdentifier): AbstractCommand
    {
        $commandMap = array_column(CommandEnum::cases(), 'value');

        return $commandMap[$commandIdentifier];
    }
}
