<?php

namespace App\States\Poll;

use App\Enums\StateEnum;
use App\Models\Subject;
use App\States\AbstractState;
use App\States\UserContext;
use App\States\UserState;

class SubjectChoiceState extends AbstractState implements UserState
{
    private const StateEnum STATE = StateEnum::POLL_SUBJECT_CHOICE;

    public function handleInput(string $input, UserContext $context): void
    {
        // Get next state by callback
        $state = $this->getState($input);

        // Update user step and flow
        $this->user->updateFlow(self::STATE, $input);
        $this->updateState($state, $context);

        // Send message to chat
        $sender = $state->sender($this->request, $this->messageSender, $this->senderService);
        $sender->send();
    }

    private function getState(string $input): StateEnum
    {
        $subject = Subject::where('code', $input)->first();

        return $subject->has_child ? self::STATE : StateEnum::POLL_THEME_WAITING;
    }
}
