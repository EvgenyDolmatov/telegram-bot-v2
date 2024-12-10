<?php

namespace App\Handlers;

use App\Handlers\Message\AbstractHandler;
use App\Handlers\Message\CommandHandler;
use App\Handlers\Message\StateHandler;
use App\Repositories\RequestRepository;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class MessageStrategy
{
    private AbstractHandler $handler;

    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly Request $request
    ) {
    }

    public function defineHandler(): self
    {
        $message = $this->getMessage();

        if (str_starts_with($message, '/')) {
            $handler = new CommandHandler($this->telegramService, $this->request);
        } else {
            $handler = new StateHandler($this->telegramService, $this->request);
        }

        return $this->setHandler($handler);
    }

    public function process(): void
    {
        $this->handler->handle($this->getMessage());
    }

    private function setHandler(AbstractHandler $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    private function getMessage(): string
    {
        return (new RequestRepository($this->request))->getDto()->getText();
    }
}
