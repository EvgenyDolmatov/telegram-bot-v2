<?php

namespace App\States\Poll;

use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class DifficultyChoiceState extends AbstractState implements UserState
{
    public function handleInput(string $input, UserContext $context): void
    {
        $pollState = PollEnum::from($input)->toState();
        $this->updateState($pollState, $context);

        $state = StateEnum::from($pollState);
        $sender = $state->sender($this->request, $this->messageSender, $this->senderService);
        $sender->process();
    }
}
