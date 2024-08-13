<?php

namespace App\Http\Controllers;

use App\Constants\CallbackConstants;
use App\Constants\CommandConstants;
use App\Helpers\StepAction;
use App\Models\TrashMessage;
use App\Models\User;
use App\Models\UserFlow;
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
            $chatDto = $requestRepository->convertToChat();

            Log::debug('USER: ' . $messageDto->getId() . ' : ' . $messageDto->getText());

            TrashMessage::add($chatDto, $messageDto, true);

            $user = User::getOrCreate($requestRepository);

            /** Select "/start" command */
            if ($messageDto->getText() === CommandConstants::START) {
                if (!$stepHelper->canContinue()) {
                    $stepHelper->subscribeToCommunity();
                    return;
                }

                $stepHelper->mainChoice();
                $user->changeState($request);
                return;
            }

            /** Select "/help" command */
            if ($messageDto->getText() === CommandConstants::HELP) {
                $stepHelper->help();
                $user->changeState($request);
                return;
            }

            /** Support button */
            if ($messageDto->getText() === CallbackConstants::SUPPORT) {
                $stepHelper->support();
                return;
            }

            /** User steps flow */
            $user->stateHandler($request, $stepHelper, $messageDto->getText());

            if ($messageDto->getText() === CallbackConstants::REPEAT_FLOW) {
                $lastFlow = $user->getLastFlow();
                UserFlow::create([
                    'user_id' => $lastFlow->user_id,
                    'flow' => $lastFlow->flow,
                    'is_completed' => 0,
                ]);

                $stepHelper->responseFromAi();
            }
        }
    }
}
