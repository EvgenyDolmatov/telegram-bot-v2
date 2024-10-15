<?php

namespace App\Enums;

use App\Commands\AccountCommand;
use App\Commands\AdminCommand;
use App\Commands\CommandInterface;
use App\Commands\HelpCommand;
use App\Commands\StartCommand;

enum CommandEnum: string
{
    case ACCOUNT = 'account';
    case ADMIN = 'admin';
    case HELP = 'help';
    case START = 'start';

    public function getCommand(): CommandInterface
    {
        return match ($this) {
            self::START => new StartCommand(),
            self::HELP => new HelpCommand(),
            self::ADMIN => new AdminCommand(),
            self::ACCOUNT => new AccountCommand(),
        };
    }
}
