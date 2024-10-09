<?php

namespace App\Handlers;

use App\Enums\SurveyCallbackEnum;
use App\Models\User;
use App\Models\UserFlow;
use App\Repositories\RequestRepository;
use App\Services\StateService;

class StateHandler extends AbstractHandler
{
    public function handle(string $message): void
    {
        $request = $this->request;
        $helper = $this->helper;

        $requestRepository = new RequestRepository($request);
        $user = User::getOrCreate($requestRepository);

        $stateService = new StateService($request, $user, $helper, $message);
        $stateService->switchState();

        if ($message === SurveyCallbackEnum::REPEAT_FLOW->value) {
            $lastFlow = $user->getLastFlow();

            if ($lastFlow) {
                UserFlow::create([
                    'user_id' => $lastFlow->user_id,
                    'flow' => $lastFlow->flow,
                    'is_completed' => 0,
                ]);

                $helper->responseFromAi();
            }

            // TODO: Create some message about quiz repeat...
        }

        // TODO: Need to do something with index 9...
        if ($user->states->contains(9)) {
            $helper->adminNewsletterConfirmation();
        }
    }
}
