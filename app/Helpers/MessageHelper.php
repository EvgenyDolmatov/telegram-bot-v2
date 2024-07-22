<?php

namespace App\Helpers;

use App\Dto\ButtonDto;
use App\Dto\MessageDto;
use App\Services\SendMessageService;
use App\Services\TelegramService;

readonly class MessageHelper
{
    public function __construct(
        private TelegramService $telegramService,
        private MessageDto $messageDto
    ) {
    }

    public function messageService(): SendMessageService
    {
        return $this->telegramService->messageService;
    }

    public function messageHandler(): void
    {
        $text = $this->messageDto->getText();
        switch ($text) {
            case '/start':
                $buttons = [
                    new ButtonDto('var1', 'VAR 1'),
                    new ButtonDto('var2', 'VAR 2'),
                ];
                $this->messageService()->sendMessage('choose button', $buttons);
                break;
            case '/poll':
                $options = [
                    ['text' => 'Option 1'],
                    ['text' => 'Option 2'],
                ];
                $this->messageService()->sendPoll('Выберите вариант', $options);
                break;
            default:
                break;
        }
    }
}
