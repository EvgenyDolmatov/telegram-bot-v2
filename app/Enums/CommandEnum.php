<?php

namespace App\Enums;

use App\Commands\AbstractCommand;
use App\Commands\DefaultCommand;
use App\Commands\StartCommand;

enum CommandEnum: string
{
    case ACCOUNT = '/account';
    case ADMIN = '/admin';
    case HELP = '/help';
    case START = '/start';

    public function retrieve(): AbstractCommand
    {
        return match ($this) {
            self::START => new StartCommand(),
            default => new DefaultCommand()
        };
    }
}
