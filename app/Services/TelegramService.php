<?php

namespace App\Services;

use App\Dto\ChatDto;
use App\Dto\MessageDto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private Request $request;
    public string $token;
    public ChatDto $chat;
    public SendMessageService $messageService;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->token = config('services.telegram.token');
        $this->chat = $this->getChatDto();
        $this->messageService = new SendMessageService($this);
    }

    public function getChatDto(): ?ChatDto
    {
        $chat = null;
        try {
            $payload = null;
            if ($this->request->has('message')) {
                $payload = $this->request->input('message')['chat'];
            }

            if ($this->request->has('callback_query')) {
                $payload = $this->request->input('callback_query')['message']['chat'];
            }

            if ($payload !== null) {
                $chat = new ChatDto(
                    $payload['id'],
                    $payload['username'],
                    $payload['type'],
                    $payload['first_name'],
                    $payload['last_name']
                );
            }
        } catch (\Throwable $throwable) {
            Log::error('ERROR: The $payload must not be null or empty.', [
                'message' => $throwable->getMessage(),
            ]);
        }

        return $chat;
    }

    public function getMessageDto(): ?MessageDto
    {
        $message = null;

        try {
            if ($this->request->has('message')) {
                $payload = $this->request->input('message');

                $message = new MessageDto(
                    $payload['message_id'],
                    $payload['text'],
                );
            }

            if ($this->request->has('callback_query')) {
                $payload = $this->request->input('callback_query');

                $message = new MessageDto(
                    $payload['message']['message_id'],
                    $payload['data'],
                );
            }

        } catch (\Throwable $throwable) {
            Log::error('ERROR: The $payload must not be null or empty.', [
                'message' => $throwable->getMessage(),
            ]);
        }

        return $message;
    }
}
