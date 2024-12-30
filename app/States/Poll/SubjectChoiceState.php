<?php

namespace App\States\Poll;

use App\Enums\CallbackEnum;
use App\Enums\StateEnum;
use App\Models\Subject;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class SubjectChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::PollSubjectChoice;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input, self::STATE);

        // Update user step and flow
        $this->user->updateFlow(self::STATE, $input);
        $this->updateState($state, $context);

        // Send message to chat
        $this->sendMessage($state);
    }

    protected function getState(string $input, StateEnum $baseState): StateEnum
    {
        if ($input === CallbackEnum::Back->value) {
            return $baseState->backState();
        }

        if (!$subject = Subject::where('code', $input)->first()) {
            return self::STATE;
        }

        return $subject->has_child
            ? self::STATE
            : StateEnum::PollThemeWaiting;
    }
}
