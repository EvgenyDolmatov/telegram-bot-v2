<?php

namespace App\Commands;


use App\Services\SenderService;

interface CommandInterface
{
    public function execute(SenderService $senderService): void;
}
