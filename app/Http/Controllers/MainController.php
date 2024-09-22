<?php

namespace App\Http\Controllers;

use App\Enums\CommonCallbackEnum;
use App\Enums\SurveyCallbackEnum;
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

        Log::debug(json_encode($request->all()));

        if ($request->hasAny(['message', 'callback_query'])) {
            $stepHelper = new StepAction($telegram, $request);

            $chatDto = $requestRepository->convertToChat();
            $messageDto = $requestRepository->convertToMessage();
            $message = $messageDto->getText();

            Log::debug('USER: ' . $messageDto->getId() . ' : ' . $message);

            TrashMessage::add($chatDto, $messageDto, true);

            $user = User::getOrCreate($requestRepository);

            /** Command handler */
            if (str_starts_with($message, '/')) {
                $user->commandHandler($request, $stepHelper, $message);
                return;
            }

            /** Button callback handler */
            $buttonCallbacks = array_column(CommonCallbackEnum::cases(), 'value');
            if (in_array($message, $buttonCallbacks)) {
                $user->callbackHandler($stepHelper, $message);
                return;
            }

            /** User steps flow */
            $user->stateHandler($request, $stepHelper, $message);

            if ($message === SurveyCallbackEnum::REPEAT_FLOW->value) {
                $lastFlow = $user->getLastFlow();

                if ($lastFlow) {
                    UserFlow::create([
                        'user_id' => $lastFlow->user_id,
                        'flow' => $lastFlow->flow,
                        'is_completed' => 0,
                    ]);

                    $stepHelper->responseFromAi();
                }

                // TODO: Create some message about quiz repeat...
            }

            // TODO: Need to do something with index 9...
            if ($user->states->contains(9)) {
                $stepHelper->adminNewsletterConfirmation();
            }
        }
    }
}
