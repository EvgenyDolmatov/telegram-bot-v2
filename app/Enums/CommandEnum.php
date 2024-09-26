<?php

namespace App\Enums;

enum CommandEnum: string
{
    case ACCOUNT = '/account';
    case ADMIN = '/admin';
    case HELP = '/help';
    case START = '/start';
}
