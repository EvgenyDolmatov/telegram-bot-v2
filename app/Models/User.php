<?php

namespace App\Models;

use App\Constants\CallbackConstants;
use App\Constants\CommandConstants;
use App\Constants\StateConstants;
use App\Constants\TransitionConstants;
use App\Helpers\StepAction;
use App\Repositories\RequestRepository;
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

    public function getCurrentState(): State
    {
        return $this->states->first();
    }

    public function getNextState(): State
    {
        $currentState = $this->getCurrentState();
        $transition = Transition::where('source', $currentState->code)->first();

        return State::where('code', $transition->next)->first();
    }

    public function getPrevState(): State
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

    /**
     * Change user state
     *
     * @param Request $request
     * @param string $destination
     * @return void
     */
    /*    public function changeState(Request $request, string $destination = TransitionConstants::NEXT): void
        {
            $requestRepository = new RequestRepository($request);
            $messageDto = $requestRepository->convertToMessage();

            $userStates = $this->states;
            $startState = State::where('code', StateConstants::START)->first();

            if ($userStates->count()) {
                $currentState = $this->states->first();
                $nextState = $this->getNextStateByDestination($currentState, $destination);

                $this->states()->detach();
            }

            $this->updateFlow($messageDto->getText());

            if (isset($nextState)) {
                $this->states()->attach($nextState->id);
            } else {
                $this->states()->attach($startState->id);
            }
        }*/

    public function hasAnyState(): bool
    {
        return $this->states()->count();
    }

    public function changeState(Request $request, string $destination = TransitionConstants::NEXT): void
    {
        $requestRepository = new RequestRepository($request);
        $messageDto = $requestRepository->convertToMessage();
        $message = $messageDto->getText();

        if ($message === CommandConstants::START) {
            $startState = State::where('code', StateConstants::START)->first();

            if ($this->hasAnyState()) {
                $this->states()->detach();
            }

            if ($userFlow = $this->getOpenedFlow()) {
                $userFlow->delete();
            }

            $this->states()->attach($startState->id);

            return;
        }

        if ($destination === TransitionConstants::NEXT) {
            $currentState = $this->getCurrentState();
            $nextState = $this->getNextStateByDestination($destination);

            // Update user flow
            $userFlow = $this->getOpenedFlow();
            if ($userFlow) {
                $userFlowArray = json_decode($userFlow->flow, true);
                $userFlowArray[$currentState->code] = $message;
                $userFlow->flow = json_encode($userFlowArray);
                $userFlow->save();
            } else {
                UserFlow::create([
                    'user_id' => $this->id,
                    'flow' => json_encode([$currentState->code => $message])
                ]);
            }

            // Change user state
            $this->states()->detach();
            $this->states()->attach($nextState->id);
        }

        if ($destination === TransitionConstants::BACK) {
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

            }

            // Change user state
            $this->states()->detach();
            $this->states()->attach($nextState->id);
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
        if ($currentState = $this->states->first()) {
            /** Step 1: Show start choice */
            if ($currentState->code === StateConstants::START) {
                if (!in_array($message, $currentState->prepareCallbackItems())) {
                    $stepAction->start();
                    $this->changeState($request);
                    return;
                }

                if ($message === CallbackConstants::SUPPORT) {
                    $stepAction->support();
                    $this->changeState($request);
                    return;
                }

                if ($message === CallbackConstants::CREATE_SURVEY) {
                    $stepAction->selectSurveyType();
                    $this->changeState($request);
                    return;
                }
            }

            /** Step 2: Show survey type choice */
            if ($currentState->code === StateConstants::TYPE_CHOICE) {
                if (!in_array($message, $currentState->prepareCallbackItems())) {
                    $stepAction->selectSurveyType();
                    $this->changeState($request, TransitionConstants::SOURCE);
                    return;
                }

                if (in_array($message, $currentState->prepareCallbackItems())) {
                    $stepAction->selectAnonymity();
                    $this->changeState($request);
                    return;
                }

                if ($message === TransitionConstants::BACK) {
                    $stepAction->start();
                    $this->changeState($request, TransitionConstants::BACK);
                    return;
                }
            }

            /** Step 3: Show is anonymous survey choice */
            if ($currentState->code === StateConstants::ANON_CHOICE) {
                if (!in_array($message, $currentState->prepareCallbackItems())) {
                    $stepAction->selectAnonymity();
                    $this->changeState($request, TransitionConstants::SOURCE);
                    return;
                }

                if (in_array($message, $currentState->prepareCallbackItems())) {
                    $stepAction->selectDifficulty();
                    $this->changeState($request);
                    return;
                }

                if ($message === TransitionConstants::BACK) {
                    $stepAction->selectSurveyType();
                    $this->changeState($request, TransitionConstants::BACK);
                    return;
                }
            }

            /** Step 4: Show survey difficulty choice */
            if ($currentState->code === StateConstants::DIFFICULTY_CHOICE) {
                if (!in_array($message, $currentState->prepareCallbackItems())) {
                    $stepAction->selectDifficulty();
                    $this->changeState($request, TransitionConstants::SOURCE);
                    return;
                }

                if (in_array($message, $currentState->prepareCallbackItems())) {
                    $stepAction->selectSector();
                    $this->changeState($request);
                    return;
                }

                if ($message === TransitionConstants::BACK) {
                    $stepAction->selectAnonymity();
                    $this->changeState($request, TransitionConstants::BACK);
                    return;
                }
            }

            /** Step 5: Show sector choice */
            if ($currentState->code === StateConstants::SECTOR_CHOICE) {
                $callbackNames = array_map(fn($sector) => $sector['code'], Sector::all()->toArray());

                if (!in_array($message, $callbackNames)) {
                    $stepAction->selectSector();
                    $this->changeState($request, TransitionConstants::SOURCE);
                    return;
                }

                if (in_array($message, $callbackNames)) {
                    $sector = Sector::where('code', $message)->first();
                    $stepAction->selectSubject($sector);
                    $this->changeState($request);
                    return;
                }

                if ($message === TransitionConstants::BACK) {
                    $stepAction->selectDifficulty();
                    $this->changeState($request, TransitionConstants::BACK);
                    return;
                }
            }

            /** Step 6: Show subject choice */
            if ($currentState->code === StateConstants::SUBJECT_CHOICE) {
                if ($userSector = $this->getSelectedSector()) {
                    $callbackNames = array_map(
                        fn($subject) => $subject['code'],
                        $userSector->subjects()->get()->toArray()
                    );

                    if (!in_array($message, $callbackNames)) {
                        $stepAction->selectSubject($userSector);
                        $this->changeState($request, TransitionConstants::SOURCE);
                        return;
                    }

                    if (in_array($message, $callbackNames)) {
                        $parentSubject = Subject::where('code', $message)->first();
                        if ($parentSubject->hasChild()) {
                            $stepAction->selectChildSubject($parentSubject);
                            $this->changeState($request);
                            return;
                        }

                        $stepAction->waitingThemeRequest();
                        $this->changeState($request);
                        return;
                    }

                    if ($message === TransitionConstants::BACK) {
                        $stepAction->selectSector();
                        $this->changeState($request, TransitionConstants::BACK);
                        return;
                    }
                }
            }

            /** Step 7: Waiting user request */
            if ($currentState->code === StateConstants::THEME_REQUEST) {
                $stepAction->responseFromAi();
                $this->changeState($request);
            }
        }
    }
}
