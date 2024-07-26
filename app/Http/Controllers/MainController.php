<?php

namespace App\Http\Controllers;

use App\Builder\Message\MessageBuilder;
use App\Builder\Sender;
use App\Constants\ButtonConstants;
use App\Constants\ButtonKeyConstants;
use App\Repositories\RequestRepository;
use App\Services\SendMessageService;
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

        if ($request->hasAny(['message', 'callback_query'])) {
            $sender = (new Sender())->setBuilder(new MessageBuilder());
            $messageDto = $requestRepository->convertToMessage();

            Log::debug('USER TEXT: ' . $messageDto->getText());

            if ($messageDto->getText() === '/start') {
                $text = 'Привет! Выбери вариант:';
                $buttons = [
                    ButtonConstants::SUPPORT,
                    ButtonConstants::CREATE_SURVEY
                ];

                $message = $sender->createMessageWithButtons($text, $buttons);
                (new SendMessageService($request, $telegram, $message))->send();
            }
        }
    }
}
