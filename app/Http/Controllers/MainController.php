<?php

namespace App\Http\Controllers;

use App\Handlers\MessageStrategy;
use App\Models\TrashMessage;
use App\Repositories\RequestRepository;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MainController extends Controller
{
    public function webhook(Request $request): void
    {
        $telegram = new TelegramService();
        $telegram->resetQueue();
        $requestRepository = new RequestRepository($request);

        Log::debug(json_encode($request->all()));

        if ($request->hasAny(['message', 'callback_query'])) {
            $chatDto = $requestRepository->convertToChat();
            $messageDto = $requestRepository->convertToMessage();
            $message = $messageDto->getText();

            Log::debug('USER: ' . $messageDto->getId() . ' : ' . $message);
            TrashMessage::add($chatDto, $messageDto, true);

            $strategy = new MessageStrategy($telegram, $request);
            $strategy->defineHandler()->process();
        }
    }
}
