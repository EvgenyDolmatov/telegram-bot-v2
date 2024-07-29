<?php

namespace App\Http\Controllers;

use App\Constants\CommandConstants;
use App\Helpers\StepAction;
use App\Models\User;
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
            Log::debug('USER: ' . $messageDto->getId() . ' : ' . $messageDto->getText());

            $user = User::getOrCreate($requestRepository);

            /** Select "/start" command */
            if ($messageDto->getText() === CommandConstants::START) {
                $stepHelper->start();
                return;
            }

            /** Select "/help" command */
            if ($messageDto->getText() === CommandConstants::HELP) {
                $stepHelper->help();
                return;
            }

            /** User steps flow */
            $user->stateHandler($stepHelper, $messageDto->getText());
        }
    }
}
