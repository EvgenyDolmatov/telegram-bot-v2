<?php

namespace App\Services;

use App\Builder\Message\Message;
use App\Constants\CommonConstants;
use App\Repositories\RequestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

readonly class SendMessageService
{
    public function __construct(
        private Request         $request,
        private TelegramService $telegramService,
        private Message         $message
    )
    {
    }

    public function send(): void
    {
        $url = CommonConstants::TELEGRAM_BASE_URL . $this->telegramService->token . '/sendMessage';
        $chat = (new RequestRepository($this->request))->convertToChat();
        $buttons = $this->message->getButtons();

        $body = [
            'chat_id' => $chat->getId(),
            'parse_mode' => 'html',
            'text' => $this->message->getText()
        ];

        if (count($buttons) !== 0) {
            foreach ($buttons as $button) {
                $body['reply_markup']['inline_keyboard'][] = [
                    [
                        'text' => $button['text'],
                        'callback_data' => $button['callback_data'],
                    ]
                ];
            }
        }

        Http::post($url, $body);
    }

//    public function sendPoll(string $question, array $options): void
//    {
//        $url = self::BASE_URL . $this->telegramService->token . '/sendPoll';
//        $body = [
//            'chat_id' => $this->telegramService->chat->getId(),
//            'question' => $question,
//            'options' => $options,
//        ];
//
//        $response = Http::post($url, $body);
//        Log::debug('BOT: ' . $response);
//    }
}
