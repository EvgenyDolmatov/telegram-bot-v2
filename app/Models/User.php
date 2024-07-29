<?php

namespace App\Models;

use App\Constants\CallbackConstants;
use App\Constants\StateConstants;
use App\Constants\StepConstants;
use App\Dto\UserDto;
use App\Entities\Message\UserEntity;
use App\Helpers\StepAction;
use App\Repositories\RequestRepository;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'tg_user_id',
        'username',
        'first_name',
        'last_name',
        'is_bot'
    ];

    public function states()
    {
        return $this->belongsToMany(State::class, 'user_states');
    }

    public function changeState(string $stateCode): void
    {
        $state = State::where('code', $stateCode)->first();
        if ($this->states->count()) {
            $this->states()->detach();
        }

        $this->states()->attach($state->id);
    }

    public static function getOrCreate(RequestRepository $repository): User
    {
        $userDto = $repository->convertToUser();
        $user = User::where('tg_user_id', $userDto->getId())->first();

        if ($user === null) {
            $user = User::create([
                'tg_user_id' => $userDto->getId(),
                'username' => $userDto->getUsername(),
                'is_bot' => $userDto->getIsBot(),
                'first_name' => $userDto->getFirstName(),
                'last_name' => $userDto->getLastName(),
            ]);
        }

        return $user;
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
                        $stepAction->selectSector();
                        break;
                    default:
                        $stepAction->selectAnonymity();
                }
            }
        }
    }
}
