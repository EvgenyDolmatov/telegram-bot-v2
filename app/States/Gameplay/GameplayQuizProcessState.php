<?php

namespace App\States\Gameplay;

use App\Enums\StateEnum;
use App\Repositories\Telegram\Response\PollAnswerRepository;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;
use Illuminate\Support\Facades\Log;

class GameplayQuizProcessState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::GameplayQuizProcess;

    public function handleInput(string $input, UserContext $context): void
    {
        //
    }
}
