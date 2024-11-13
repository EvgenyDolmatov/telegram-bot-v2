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
    /**
     * @throws \Exception
     */
    public function webhook(Request $request): void
    {
        $telegram = new TelegramService();
        $telegram->resetQueue();

        Log::debug(json_encode($request->all()));

        if ($request->hasAny(['message', 'callback_query'])) {
            $messageDto = (new RequestRepository($request))->getDto();

            Log::debug('USER: ' . $messageDto->getId() . ' : ' . $messageDto->getText());
            TrashMessage::add($messageDto->getChat(), $messageDto, true);

            $strategy = new MessageStrategy($telegram, $request);
            $strategy->defineHandler()->process();
        }
    }
}
