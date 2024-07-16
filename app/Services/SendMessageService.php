<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SendMessageService
{
    const string BASE_URL = 'https://api.telegram.org/bot';

    public function __construct(
        private readonly TelegramService $telegramService
    )
    {}

    public function sendMessage(string $message): void
    {
        $url = self::BASE_URL . $this->telegramService->token . '/sendMessage';
        $body = [
            'chat_id' => $this->telegramService->chat->getId(),
            'parse_mode' => 'html',
            'text' => $message,
        ];

        Http::post($url, $body);
    }
}
