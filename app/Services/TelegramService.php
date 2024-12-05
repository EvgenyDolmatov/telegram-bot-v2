<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    public const string ROOT_URL = 'https://api.telegram.org';
    public const string BASE_URL = self::ROOT_URL . '/bot';
    public string $token;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
    }

    public function resetQueue(): void
    {
        $url = self::BASE_URL. $this->token . '/setWebhook';
        $data = [
            'url' => env('APP_URL') . '/webhook',
            'drop_pending_updates' => true
        ];

        Http::post($url, $data);
    }
}
