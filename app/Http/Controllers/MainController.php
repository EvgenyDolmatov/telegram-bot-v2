<?php

namespace App\Http\Controllers;

use App\Constants\ButtonConstants;
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
            Log::debug('USER TEXT: ' . $messageDto->getText());

            $user = User::getOrCreate($requestRepository);
            $userState = $user->states->first();

            if ($messageDto->getText() === '/start') {
                $stepHelper->start();
                return;
            }

            if ($messageDto->getText() === '/help') {
                $stepHelper->help();
                return;
            }


            if ($userState) {
                if ($userState->code === 'start') {
                    if (in_array($messageDto->getText(), ['create_survey', 'support'])) {
                        $stepHelper->help();
                    } else {
                        $stepHelper->start();
                    }
                }
            }
        }
    }
}
