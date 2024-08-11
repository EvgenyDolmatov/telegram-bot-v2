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

    public function getLastFlow(): UserFlow
    {
        return UserFlow::where('user_id', $this->id)->where('is_completed', 1)->latest()->first();
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
            default:
                $previousState = $this->getCurrentState();
                $currentState = $this->getNextState();

                if ($message === TransitionConstants::BACK) {
                    if ($userFlowData = $this->getFlowData()) {
                        if (isset($userFlowData[$this->getPrevState()->code])) {
                            unset($userFlowData[$this->getPrevState()->code]);

                            $userFlow = $this->getOpenedFlow();
                            if (empty($userFlowData && $userFlow)) {
                                $userFlow->delete();
                            } else {
                                $userFlow->flow = json_encode($userFlowData);
                                $userFlow->save();
                            }
                        }
                    }

                    $this->states()->detach();
                    $this->states()->attach($this->getPrevState()->id);
                    return;
                } else {
                    // если ввели что-то непонятное
                    if (
                        $previousState->code !== StateConstants::THEME_REQUEST &&
                        !in_array($message, $previousState->prepareCallbackItems($this))
                    ) { Log::debug('HERE'); return; }

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
