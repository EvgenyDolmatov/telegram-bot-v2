<?php

namespace App\Handlers;

use App\Enums\StateEnum;
use App\Handlers\Message\StateMessageHandler;
use App\Repositories\Telegram\Request\AbstractRepository as AbstractMessageRepository;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Repositories\Telegram\Response\PollAnswerRepository;
use App\Senders\Gameplay\GameplayQuizProcessSender;
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
            StateEnum::GameplayQuizProcess->sender($this->repository, $this->telegramService, $this->user)->send();

//            $handler = new PollAnswerHandler($this->telegramService, $this->repository);
//            $handler->handle();
        }
    }
}
