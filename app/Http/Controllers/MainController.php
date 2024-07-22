<?php

namespace App\Http\Controllers;

use App\Dto\ButtonDto;
use App\Helpers\MessageHelper;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MainController extends Controller
{
    public function webhook(Request $request): void
    {
        if ($request->hasAny(['message', 'callback_query'])) {
            $tg = new TelegramService($request);
            $messageHelper = new MessageHelper($tg, $tg->getMessageDto());

            $messageHelper->messageHandler();
        }
    }
}
