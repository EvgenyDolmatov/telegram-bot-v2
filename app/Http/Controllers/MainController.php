<?php

namespace App\Http\Controllers;

use App\Helpers\StepAction;
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

        if ($request->hasAny(['message', 'callback_query'])) {
            $stepHelper = new StepAction($telegram, $request);
            $messageDto = $requestRepository->convertToMessage();
            Log::debug('USER TEXT: ' . $messageDto->getText());

            if ($messageDto->getText() === '/start') {
                $stepHelper->start();
            }

            if ($messageDto->getText() === '/help') {
                $stepHelper->help();
            }
        }
    }
}
