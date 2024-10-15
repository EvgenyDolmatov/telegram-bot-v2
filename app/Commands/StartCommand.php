<?php

namespace App\Commands;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Services\SenderService;

class StartCommand implements CommandInterface
{
    public function execute(SenderService $senderService): void
    {
        $message = (new MessageSender())->setBuilder(new MessageBuilder());
        $message = $message->createMessage("Hello!");

        $senderService->sendMessage($message);
    }
}
