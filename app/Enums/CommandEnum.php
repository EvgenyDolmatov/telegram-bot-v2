<?php

namespace App\Enums;

enum CommandEnum: string
{
    case ACCOUNT = 'account';
    case ADMIN = 'admin';
    case HELP = 'help';
    case START = 'start';

    public function toState(): StateEnum
    {
        return match ($this) {
            self::ACCOUNT => StateEnum::ACCOUNT,
            self::ADMIN => StateEnum::ADMIN,
            self::HELP => StateEnum::HELP,
            self::START => StateEnum::START,
        };
    }

    public function getCommand(): string
    {
        return match ($this) {
            self::ACCOUNT => '/' . self::ACCOUNT->value,
            self::ADMIN => '/' . self::ADMIN->value,
            self::HELP => '/' . self::HELP->value,
            self::START => '/' . self::START->value,
        };
    }
}
