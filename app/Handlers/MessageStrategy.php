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
use Illuminate\Support\Facades\Log;

class MessageStrategy
{
    private AbstractHandler $handler;

    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly Request $request
    ) {
    }

    /**
     * @throws \Exception
     */
    public function defineHandler(): self
    {
        $message = $this->getMessage();
//        $handler = new StateHandler($this->telegramService, $this->request);

        Log::debug($message);

        if (str_starts_with($message, '/')) {
            $handler = new CommandHandler($this->telegramService, $this->request);
        } else {
            $handler = new StateHandler($this->telegramService, $this->request);
        }



//        $buttonCallbacks = array_column(CommonCallbackEnum::cases(), 'value');
//        if (in_array($message, $buttonCallbacks)) {
//            $handler = new CallbackHandler($this->telegramService, $this->request);
//        }

        return $this->setHandler($handler);
    }

    /**
     * @throws \Exception
     */
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
     * @throws \Exception
     */
    private function getMessage(): string
    {
        return (new RequestRepository($this->request))->getDto()->getText();
    }
}
