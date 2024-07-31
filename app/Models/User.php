<?php

namespace App\Models;

use App\Constants\CallbackConstants;
use App\Constants\CommandConstants;
use App\Constants\StateConstants;
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

    /**
     * Change user state
     *
     * @param Request $request
     * @param string $direction
     * @return void
     */
    public function changeState(Request $request, string $direction = 'next'): void
    {
        $requestRepository = new RequestRepository($request);
        $messageDto = $requestRepository->convertToMessage();

        $userStates = $this->states;
        $startState = State::where('code', StateConstants::START)->first();

        if ($userStates->count()) {
            $userState = $this->states->first();
            $stateTransition = Transition::where('source', $userState->code)->first();

            if ($direction === StateConstants::START) {
                $nextState = $startState;
            }

            if ($direction === 'next') {
                $nextState = State::where('code', $stateTransition->next)->first();
            }

            if ($direction === 'prev') {
                $nextState = State::where('code', $stateTransition->back)->first();
            }

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
            /** Step 1: Start choice */
            if ($currentState->code === StateConstants::START) {
                switch ($message) {
                    case CallbackConstants::SUPPORT:
                        $stepAction->support();
                        break;
                    case CallbackConstants::CREATE_SURVEY:
                        $stepAction->selectSurveyType();
                        break;
                    default:
                        $stepAction->start();
                }
            }

            /** Step 2: Survey type choice */
            if ($currentState->code === StateConstants::TYPE_CHOICE) {
                switch ($message) {
                    case CallbackConstants::TYPE_QUIZ:
                    case CallbackConstants::TYPE_SURVEY:
                        $stepAction->selectAnonymity();
                        break;
                    default:
                        $stepAction->selectSurveyType();
                }
            }

            /** Step 3: Is anonymous survey choice */
            if ($currentState->code === StateConstants::ANON_CHOICE) {
                switch ($message) {
                    case CallbackConstants::IS_ANON:
                    case CallbackConstants::IS_NOT_ANON:
                        $stepAction->selectDifficulty();
                        break;
                    default:
                        $stepAction->selectAnonymity();
                }
            }

            /** Step 4: Survey difficulty choice */
            if ($currentState->code === StateConstants::DIFFICULTY_CHOICE) {
                switch ($message) {
                    case CallbackConstants::LEVEL_EASY:
                    case CallbackConstants::LEVEL_MIDDLE:
                    case CallbackConstants::LEVEL_HARD:
                        $stepAction->selectSector();
                        break;
                    default:
                        $stepAction->selectDifficulty();
                }
            }

            /** Step 5: Sector choice */
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
            }

            /** Step 6: Subject choice */
            if ($currentState->code === StateConstants::SUBJECT_CHOICE) {
                //
            }
        }
    }
}
