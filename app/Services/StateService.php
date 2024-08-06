<?php

namespace App\Services;

use App\Constants\CallbackConstants;
use App\Constants\StateConstants;
use App\Constants\TransitionConstants;
use App\Helpers\StepAction;
use App\Models\Sector;
use App\Models\State;
use App\Models\Transition;
use App\Models\User;
use Illuminate\Http\Request;


class StateService
{
    public function __construct(
        private readonly Request    $request,
        private readonly User       $user,
        private readonly StepAction $stepAction,
        private readonly string     $message
    )
    {

    }

    public function callback(StepAction $stepAction, string $trigger, array $args = null)
    {
        return $args
            ? call_user_func([$stepAction, $trigger], $args)
            : call_user_func([$stepAction, $trigger]);
    }

    public function switchState(): void
    {
        $user = $this->user;
        $stepAction = $this->stepAction;
        $message = $this->message;

        if ($currentState = $user->getCurrentState()) {
            $args = null;

            if ($currentState->code === StateConstants::SUBJECT_CHOICE) {
                $args = Sector::find(1);
            }

            if (!in_array($message, $currentState->prepareCallbackItems($user))) {
                $trigger = Transition::where('source', $currentState->code)->first()->trigger;

                $this->callback($stepAction, $trigger, $args);
                $user->changeState($this->request);
            }

            if (in_array($message, $currentState->prepareCallbackItems($user))) {
                $nextState = $user->getNextState();
                $trigger = Transition::where('source', $nextState->code)->first()->trigger;

                $this->callback($stepAction, $trigger, $args);
                $user->changeState($this->request);
            }

            if ($message === TransitionConstants::BACK) {
                $prevState = $user->getPrevState();
                $trigger = Transition::where('source', $prevState->code)->first()->trigger;

                $this->callback($stepAction, $trigger, $args);
                $user->changeState($this->request);
            }
        }
    }
}
