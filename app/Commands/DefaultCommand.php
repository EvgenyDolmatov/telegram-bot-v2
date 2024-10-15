<?php

namespace App\Commands;

use App\Services\SenderService;

class DefaultCommand implements CommandInterface
{
    public function execute(SenderService $senderService): void
    {
        //
    }
}
