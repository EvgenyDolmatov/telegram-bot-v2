<?php

namespace App\Commands;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Services\SenderService;

class AdminCommand implements CommandInterface
{
    private const string MESSAGE = "Admin!";

    public function execute(SenderService $senderService): void
    {
        $message = (new MessageSender())->setBuilder(new MessageBuilder());
        $message = $message->createMessage(self::MESSAGE);

        $senderService->sendMessage($message);
    }
}
