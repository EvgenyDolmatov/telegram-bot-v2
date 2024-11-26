<?php

namespace App\States;

use App\Builder\Message\MessageBuilder;
use App\Builder\MessageSender;
use App\Services\SenderService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class AbstractState
{
    protected SenderService $senderService;
    protected MessageSender $messageSender;

    public function __construct(
        protected readonly Request $request,
        protected readonly TelegramService $telegramService
    ) {
        $this->senderService = new SenderService($request, $telegramService);
        $this->messageSender = (new MessageSender())->setBuilder(new MessageBuilder());
    }
}
