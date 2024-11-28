<?php

namespace App\States;

use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\Models\UserFlow;
use Illuminate\Support\Facades\Log;

class StartState extends AbstractState
{
    public function handleInput(string $input, UserContext $context): void
    {
        $state = PollEnum::from($input)->toState(); // create_survey => poll_type_choice
        $this->updateState($state, $context);

//        $userFlow = $this->user->getOpenedFlow();
//        if (!$userFlow) {
//            $userFlow = UserFlow::create([
//                'user_id' => $this->user->id,
//                'flow' => json_encode([])
//            ]);
//        }

        $pollItem = StateEnum::from($state);
        $sender = $pollItem->sender($this->request, $this->messageSender, $this->senderService);
        $sender->process();
    }
}
