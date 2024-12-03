<?php

namespace App\States\Poll;

use App\Constants\CommonConstants;
use App\Enums\PollEnum;
use App\Enums\StateEnum;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class AnonymityChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::POLL_ANONYMITY_CHOICE;

    public function handleInput(string $input, UserContext $context): void
    {
        // TODO: Send message again if request is wrong...
        $availableValues = [PollEnum::IS_ANON->value, PollEnum::IS_NOT_ANON->value, CommonConstants::BACK];
        if (!in_array($input, $availableValues)) {
            $this->handleRepeatSimpleInput($context, self::STATE);
            return;
        }

        $this->handleSimpleInput($input, $context, self::STATE);
    }
}
