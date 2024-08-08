<?php

namespace App\Models;

use App\Constants\CommandConstants;
use App\Constants\StateConstants;
use App\Constants\TransitionConstants;
use App\Helpers\StepAction;
use App\Repositories\RequestRepository;
use App\Services\StateService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class User extends Model
{
    protected $fillable = [
        'tg_user_id',
        'tg_chat_id',
        'username',
        'first_name',
        'last_name',
        'is_bot'
    ];

    public function states()
    {
        return $this->belongsToMany(State::class, 'user_states');
    }

    public function flows()
    {
        return $this->hasMany(UserFlow::class, 'user_id');
    }

    public static function getOrCreate(RequestRepository $repository): User
    {
        $userDto = $repository->convertToUser();
        $chatDto = $repository->convertToChat();
        $user = User::where('tg_user_id', $userDto->getId())->first();

        if ($user === null) {
            $user = User::create([
                'tg_user_id' => $userDto->getId(),
                'tg_chat_id' => $chatDto->getId(),
                'username' => $userDto->getUsername(),
                'is_bot' => $userDto->getIsBot(),
                'first_name' => $userDto->getFirstName(),
                'last_name' => $userDto->getLastName(),
            ]);
        }

        return $user;
    }

    public function getOpenedFlow(): ?UserFlow
    {
        return $this->flows->where('is_completed', 0)->first();

    }

    public function getFlowData(): ?array
    {
        $flow = $this->getOpenedFlow();

        return $flow ? json_decode($flow->flow, true) : null;

    }

    public function getCurrentState(): State
    {
        if (!$this->states->first()) {
            $startState = State::where('code', StateConstants::START)->first();
            $this->states()->attach($startState->id);
        }

        return $this->states->first();
    }

    public function getNextState(): ?State
    {
        $currentState = $this->getCurrentState();
        $transition = Transition::where('source', $currentState->code)->first();

        return State::where('code', $transition->next)->first();
    }

    public function getPrevState(): ?State
    {
        $currentState = $this->getCurrentState();
        $transition = Transition::where('source', $currentState->code)->first();

        return State::where('code', $transition->back)->first();
    }

    public function getSelectedSector(): ?Sector
    {
        $userFlowArray = json_decode($this->getOpenedFlow()->flow, true);
        $userSectorChoice = $userFlowArray[StateConstants::SECTOR_CHOICE];

        if ($userSectorChoice) {
            return Sector::where('code', $userSectorChoice)->first();
        }

        return null;
    }

    /**
     * Get next user state
     *
     * @param string $destination
     * @return State
     */
    public function getNextStateByDestination(string $destination): State
    {
        $currentState = $this->getCurrentState();
        $stateTransition = Transition::where(TransitionConstants::SOURCE, $currentState->code)->first();

        return match ($destination) {
            TransitionConstants::SOURCE => State::where('code', $stateTransition->source)->first(),
            TransitionConstants::NEXT => State::where('code', $stateTransition->next)->first(),
            TransitionConstants::BACK => State::where('code', $stateTransition->back)->first(),
            default => State::where('code', StateConstants::START)->first(),
        };
    }

    public function hasAnyState(): bool
    {
        return $this->states()->count();
    }

    /**
     * TODO: Write code for "BACK" callback
     *
     * @param Request $request
     * @return void
     */
    public function changeState(Request $request)
    {
        $requestRepository = new RequestRepository($request);
        $messageDto = $requestRepository->convertToMessage();
        $message = $messageDto->getText();

        switch ($message) {
            case CommandConstants::START:
                $startState = State::where('code', StateConstants::START)->first();

                if ($this->hasAnyState())
                    $this->states()->detach();

                if ($userFlow = $this->getOpenedFlow())
                    $userFlow->delete();

                $this->states()->attach($startState->id);
                break;
            case TransitionConstants::BACK:
                // ... code ...
                /*if ($destination === TransitionConstants::BACK) {
                    $currentState = $this->getCurrentState();
                    $nextState = $this->getNextStateByDestination($destination);

                    // Update user flow
                    $userFlow = $this->getOpenedFlow();
                    if ($userFlow) {
                        $prevState = $this->getPrevState();

                        $userFlowArray = json_decode($userFlow->flow, true);

                        if (count($userFlowArray) > 1) {
                            unset($userFlowArray[$prevState->code]);
                            $userFlow->flow = json_encode($userFlowArray);
                            $userFlow->save();
                        } else {
                            $userFlow->delete();
                        }

                    }*/
                break;
            default: // from second to last steps
                $previousState = $this->getCurrentState();
                $currentState = $this->getNextState();

                Log::debug("Previous state: " . $previousState->code);
                Log::debug("Current state: " . $currentState->code);

                if ($userFlowData = $this->getFlowData()) {
                    $userFlowData[$previousState->code] = $message;
                    $userFlow = $this->getOpenedFlow();
                    $userFlow->flow = json_encode($userFlowData);
                    $userFlow->save();
                } else {
                    UserFlow::create([
                        'user_id' => $this->id,
                        'flow' => json_encode([$previousState->code => $message])
                    ]);
                }

                $this->states()->detach();
                $this->states()->attach($currentState->id);
        }
    }

    /**
     * User steps flow by user state and choice
     *
     * @param Request $request
     * @param StepAction $stepAction
     * @param string $message
     * @return void
     */
    public function stateHandler(Request $request, StepAction $stepAction, string $message): void
    {
        $stateService = new StateService($request, $this, $stepAction, $message);
        $stateService->switchState();
    }
}
