<?php

namespace App\Services;

use App\Constants\TransitionConstants;
use App\Helpers\StepAction;
use App\Models\State;
use App\Models\Transition;
use App\Models\User;
use Illuminate\Http\Request;


readonly class StateService
{
    public function __construct(
        private Request    $request,
        private User       $user,
        private StepAction $stepAction,
        private string     $message
    )
    {

    }

    public function callback(StepAction $stepAction, string $trigger)
    {
        return call_user_func([$stepAction, $trigger]);
    }

    public function switchState(): void
    {
        $user = $this->user;
        $message = $this->message;

        if ($currentState = $user->getCurrentState()) {
            if (!in_array($message, $currentState->prepareCallbackItems($user))) {
                $this->toNextState($currentState);
            }

            if (in_array($message, $currentState->prepareCallbackItems($user))) {
                $this->toNextState($user->getNextState());
            }

            if ($message === TransitionConstants::BACK) {
                $this->toNextState($user->getPrevState());
            }
        }
    }

    public function toNextState(State $state): void
    {
        $user = $this->user;
        $stepAction = $this->stepAction;
        $trigger = Transition::where('source', $state->code)->first()->trigger;

        $user->changeState($this->request);
        $this->callback($stepAction, $trigger);
    }
}
