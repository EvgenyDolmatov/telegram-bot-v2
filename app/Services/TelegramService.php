<?php

namespace App\Services;

use App\Dto\ChatDto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private Request $request;
    public string $token;
    public ChatDto $chat;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->token = config('services.telegram.token');
        $this->chat = $this->getChatDto();
    }

    public function getChatDto(): ChatDto
    {
        try {
            if ($this->request->has('message')) {
                $payload = $this->request->input('message')['chat'];

                $chat = new ChatDto(
                    $payload['id'],
                    $payload['username'],
                    $payload['type'],
                    $payload['first_name'],
                    $payload['last_name']
                );
            }
        } catch (\Throwable $throwable) {
            Log::error('ERROR: ', [
                'message' => $throwable,
            ]);
        }

        return $chat;
    }
}
