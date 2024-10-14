<?php

namespace App\Commands;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;

class StartCommand extends AbstractCommand
{
    private const string MESSAGE = "Hello message...";

    public function execute(): void
    {
//        $this->addToTrash();
//
//        $repository = $this->repository;
//        $user = User::getOrCreate($repository);
//        $startState = State::where('code', StateConstants::START)->first();
//

        $messageSender = (new MessageSender())->setBuilder(new MessageBuilder());
        $message = $messageSender->createMessage(self::MESSAGE);

        $this->senderService->sendMessage($message);
    }
}
