<?php

namespace App\Handlers\Message;

use App\Handlers\AbstractHandler;
use App\Repositories\Telegram\Request\RepositoryInterface;
use App\Services\TelegramService;

abstract class AbstractMessageHandler extends AbstractHandler
{
    public function __construct(TelegramService $telegramService, RepositoryInterface $repository)
    {
        parent::__construct($telegramService, $repository);
    }

    abstract public function handle(string $message): void;
}
