<?php

namespace App\Services;

use App\Dto\ButtonDto;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendMessageService
{
    const string BASE_URL = 'https://api.telegram.org/bot';

    public function __construct(
        private readonly TelegramService $telegramService
    )
    {}

    public function sendMessage(string $message, array $buttons = []): void
    {
        $url = self::BASE_URL . $this->telegramService->token . '/sendMessage';
        $body = [
            'chat_id' => $this->telegramService->chat->getId(),
            'parse_mode' => 'html',
            'text' => $message,
        ];

        if (count($buttons) !== 0) {
            /** @link ButtonDto */
            foreach ($buttons as $button) {
                $body['reply_markup']['inline_keyboard'][] = [
                    [
                        'text' => $button->getText(),
                        'callback_data' => $button->getCallbackData(),
                    ]
                ];
            }
        }

        $response = Http::post($url, $body);
        Log::debug('BOT: ' . $response);
    }

    public function sendPoll(string $question, array $options): void
    {
        $url = self::BASE_URL . $this->telegramService->token . '/sendPoll';
        $body = [
            'chat_id' => $this->telegramService->chat->getId(),
            'question' => $question,
            'options' => $options,
        ];

        $response = Http::post($url, $body);
        Log::debug('BOT: ' . $response);
    }
}
