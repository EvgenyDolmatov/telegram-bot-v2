<?php

namespace App\Handlers;

use App\Repositories\Telegram\Request\AbstractRepository as AbstractMessageRepository;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Repositories\Telegram\Response\PollAnswerRepository;
use App\Services\TelegramService;

class HandlerStrategy extends AbstractHandler
{
    public function __construct(TelegramService $telegramService, RepositoryInterface $repository)
    {
        parent::__construct($telegramService, $repository);
    }

    public function defineBehavior(): void
    {
        if ($this->repository instanceof AbstractMessageRepository) {
            $messageStrategy = new MessageStrategy($this->telegramService, $this->repository);
            $messageStrategy->defineHandler()->process();
        }

        if ($this->repository instanceof PollAnswerRepository) {
            $handler = new PollAnswerHandler($this->telegramService, $this->repository);
            $handler->handle();
        }
    }
}
