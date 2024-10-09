<?php

namespace App\Handlers;

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
        if (str_starts_with($this->getMessage(), '/')) {
            $handler = new CommandHandler($this->telegramService, $this->request);
            return $this->setHandler($handler);
        }

        return $this;
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
        $requestRepository = new RequestRepository($this->request);

        return $requestRepository->convertToMessage()->getText();
    }
}
