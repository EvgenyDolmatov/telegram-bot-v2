<?php

namespace App\Enums;

use App\Commands\CommandInterface;
use App\Commands\DefaultCommand;
use App\Commands\StartCommand;

enum CommandEnum: string
{
    case ACCOUNT = '/account';
    case ADMIN = '/admin';
    case HELP = '/help';
    case START = '/start';

    public function getCommand(): CommandInterface
    {
        return match ($this) {
            self::START => new StartCommand(),
            default => new DefaultCommand()
        };
    }
}
