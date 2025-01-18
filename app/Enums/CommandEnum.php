<?php

namespace App\Enums;

enum CommandEnum: string
{
    /** Main commands */
    case Account = 'account';
    case Admin = 'admin';
    case Help = 'help';
    case Start = 'start';

    public function toState(): StateEnum
    {
        return match ($this) {
            self::Account => StateEnum::Account,
            self::Admin => StateEnum::Admin,
            self::Help => StateEnum::Help,
            self::Start => StateEnum::Start,
        };
    }

    public function getCommand(): string
    {
        return '/' . $this->value;
    }
}
