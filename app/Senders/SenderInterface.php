<?php

namespace App\Senders;

use App\Models\State;
use App\Models\User;

interface SenderInterface
{
    public function process(): void;
}
