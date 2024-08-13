<?php

namespace App\Services;

use App\Constants\CommonConstants;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    public string $token;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
    }

    public function resetQueue(): void
    {
        $url = CommonConstants::TELEGRAM_BASE_URL. $this->token . '/setWebhook';
        $data = [
            'url' => env('APP_URL') . '/webhook',
            'drop_pending_updates' => true
        ];

        Http::post($url, $data);
    }
}
