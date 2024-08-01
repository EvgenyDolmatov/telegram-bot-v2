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

    public function getCurrentState(): ?State
    {
        return $this->states->first();
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
     * @param State $currentState
     * @param string $destination
     * @return State
     */
    public function getNextState(State $currentState, string $destination): State
    {
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
    public function changeState(Request $request, string $destination = TransitionConstants::NEXT): void
    {
        $requestRepository = new RequestRepository($request);
        $messageDto = $requestRepository->convertToMessage();

        $userStates = $this->states;
        $startState = State::where('code', StateConstants::START)->first();

        if ($userStates->count()) {
            $currentState = $this->states->first();
            $nextState = $this->getNextState($currentState, $destination);

            $this->states()->detach();
        }

        $this->updateFlow($messageDto->getText());

        if (isset($nextState)) {
            $this->states()->attach($nextState->id);
        } else {
            $this->states()->attach($startState->id);
        }
    }

    /**
     * Remember user answer on the step
     *
     * @param string $message
     * @return void
     */
    public function updateFlow(string $message): void
    {
        $userState = $this->getCurrentState();
        $userFlow = $this->getOpenedFlow();

        if ($userState) {
            if ($userFlow) {
                if ($message === CommandConstants::START) {
                    $userFlow->delete();
                    return;
                }

                $userFlowArray = json_decode($userFlow->flow, true);
                $userFlowArray[$userState->code] = $message;

                $userFlow->flow = json_encode($userFlowArray);
                $userFlow->save();
                return;
            }

            UserFlow::create([
                'user_id' => $this->id,
                'flow' => json_encode([$userState->code => $message])
            ]);
        }
    }


    /**
     * User steps flow by user state and choice
     *
     * @param StepAction $stepAction
     * @param string $message
     * @return void
     */
    public function stateHandler(StepAction $stepAction, string $message): void
    {
        $currentState = $this->states->first();

        if ($currentState) {
            /** Step 1: Show start choice */
            if ($currentState->code === StateConstants::START) {
                if (!in_array($message, [CallbackConstants::CREATE_SURVEY, CallbackConstants::SUPPORT])) {
                    $stepAction->start();
                    return;
                }

                if ($message === CallbackConstants::SUPPORT) {
                    $stepAction->support();
                    return;
                }

                if ($message === CallbackConstants::CREATE_SURVEY) {
                    $stepAction->selectSurveyType();
                    return;
                }
            }

            /** Step 2: Show survey type choice */
            if ($currentState->code === StateConstants::TYPE_CHOICE) {
                switch ($message) {
                    case CallbackConstants::TYPE_QUIZ:
                    case CallbackConstants::TYPE_SURVEY:
                        $stepAction->selectAnonymity();
                        return;
                    default:
                        $stepAction->selectSurveyType();
                        return;
                }
            }

            /** Step 3: Show is anonymous survey choice */
            if ($currentState->code === StateConstants::ANON_CHOICE) {
                switch ($message) {
                    case CallbackConstants::IS_ANON:
                    case CallbackConstants::IS_NOT_ANON:
                        $stepAction->selectDifficulty();
                        return;
                    default:
                        $stepAction->selectAnonymity();
                        return;
                }
            }

            /** Step 4: Show survey difficulty choice */
            if ($currentState->code === StateConstants::DIFFICULTY_CHOICE) {
                switch ($message) {
                    case CallbackConstants::LEVEL_EASY:
                    case CallbackConstants::LEVEL_MIDDLE:
                    case CallbackConstants::LEVEL_HARD:
                        $stepAction->selectSector();
                        return;
                    default:
                        $stepAction->selectDifficulty();
                        return;
                }
            }

            /** Step 5: Show sector choice */
            if ($currentState->code === StateConstants::SECTOR_CHOICE) {
                $callbackNames = [];
                foreach (Sector::all() as $sector) {
                    $callbackNames[] = $sector->code;
                }

                if (in_array($message, $callbackNames)) {
                    $sector = Sector::where('code', $message)->first();
                    $stepAction->selectSubject($sector);
                    return;
                }

                $stepAction->selectSector();
                return;
            }

            /** Step 6: Show subject choice */
            if ($currentState->code === StateConstants::SUBJECT_CHOICE) {
                if ($userSector = $this->getSelectedSector()) {
                    $callbackNames = [];
                    foreach ($userSector->subjects as $subject) {
                        $callbackNames[] = $subject->code;
                    }

                    if (in_array($message, $callbackNames)) {
                        // If subject has child subjects
                        $parentSubject = Subject::where('code', $message)->first();
                        if ($parentSubject->hasChild()) {
                            $stepAction->selectChildSubject($parentSubject);
                            return;
                        }

                        $stepAction->waitingThemeRequest();
                        return;
                    }

                    $stepAction->selectSubject($userSector);
                }
            }

            /** Step 7: Waiting user request */
            if ($currentState->code === StateConstants::THEME_REQUEST) {
                //
            }
        }
    }
}
