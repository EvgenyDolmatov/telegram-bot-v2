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
        $state = PollEnum::from($input)->state();
        Log::debug('handleInput:' .$state );
        $this->updateState($state, $context);

//        $userFlow = $this->user->getOpenedFlow();
//        if (!$userFlow) {
//            $userFlow = UserFlow::create([
//                'user_id' => $this->user->id,
//                'flow' => json_encode([])
//            ]);
//        }




//        $pollItem = StateEnum::from($input);
//        $sender = $pollItem->sender($this->request, $this->messageSender, $this->senderService);
//
//        $sender->process();
    }
}
