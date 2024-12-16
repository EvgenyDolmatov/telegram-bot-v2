<?php

namespace App\Http\Controllers;

use App\Dto\Telegram\RequestStrategy;
use App\Handlers\MessageStrategy;
use App\Models\TrashMessage;
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

        $repository = (new RequestStrategy())->defineMessageRepository($request);
        $requestDto = $repository->getDto();

        // Prepare message to delete on next step
        TrashMessage::add(
            chatId: $requestDto->getChat()->getId(),
            messageId: $requestDto->getId(),
            isTrash: true
        );

        $strategy = new MessageStrategy($telegram, $repository);
        $strategy->defineHandler()->process();


//        if ($request->hasAny(['message', 'callback_query'])) {
//            $requestDto = (new RequestRepository($request))->getDto();

//            Log::debug('USER: ' . $requestDto->getId() . ' : ' . $requestDto->getText());
//            TrashMessage::add(
//                chatId: $requestDto->getChat()->getId(),
//                messageId: $requestDto->getId(),
//                isTrash: true
//            );

//            $strategy = new MessageStrategy($telegram, $request);
//            $strategy->defineHandler()->process();
//        }
    }
}
