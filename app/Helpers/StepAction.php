<?php

namespace App\Helpers;

use App\Builder\Message\MessageBuilder;
use App\Builder\Sender;
use App\Constants\ButtonConstants;
use App\Services\SendMessageService;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class StepAction
{
    private Sender $sender;
    private TelegramService $telegramService;
    private Request $request;

    public function __construct(TelegramService $telegramService, Request $request)
    {
        $this->sender = (new Sender())->setBuilder(new MessageBuilder());
        $this->telegramService = $telegramService;
        $this->request = $request;
    }

    /**
     * Step 1: If user pressed "Start" button
     *
     * @return void
     */
    public function start(): void
    {
        $text = 'Привет! Выбери вариант:';
        $buttons = [
            ButtonConstants::SUPPORT,
            ButtonConstants::CREATE_SURVEY
        ];

        $message = $this->sender->createMessageWithButtons($text, $buttons);
        (new SendMessageService(
            $this->request,
            $this->telegramService,
            $message)
        )->send();
    }

    public function help(): void
    {
        $text = 'Инструкция по работе с ботом:';

        $message = $this->sender->createSimpleMessage($text);
        (new SendMessageService(
            $this->request,
            $this->telegramService,
            $message)
        )->send();
    }
}
