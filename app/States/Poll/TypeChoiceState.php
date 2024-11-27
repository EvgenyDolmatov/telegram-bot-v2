<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;
use Illuminate\Support\Facades\Log;

class TypeChoiceState extends AbstractState implements UserState
{
    public function handleInput(string $input, UserContext $context): void
    {
//        $this->updateState($input, $context);
//
//        $pollItem = StateEnum::from($input);
//        $sender = $pollItem->sender($this->request, $this->messageSender, $this->senderService);
//
//        $sender->process();
        Log::debug('OK');
    }
}
