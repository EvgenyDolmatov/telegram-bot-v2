<?php

namespace App\Handlers;

use App\Enums\CommonCallbackEnum;
use App\Handlers\Message\AbstractHandler;
use App\Handlers\Message\CallbackHandler;
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
        $handler = new StateHandler($this->telegramService, $this->request);

        if (str_starts_with($message, '/')) {
            $handler = new CommandHandler($this->telegramService, $this->request);
        }

        $buttonCallbacks = array_column(CommonCallbackEnum::cases(), 'value');
        if (in_array($message, $buttonCallbacks)) {
            $handler = new CallbackHandler($this->telegramService, $this->request);
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


    /**
     * TODO: Разобраться с Request Repository. Учесть MessageDto и CallbackDto
     */
    private function getMessage(): string
    {
        $requestRepository = new RequestRepository($this->request);

        return $requestRepository->convertToMessage()->getText();
    }
}
