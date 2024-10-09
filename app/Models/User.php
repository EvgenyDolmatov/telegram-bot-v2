<?php

namespace App\Models;

use App\Constants\StateConstants;
use App\Constants\TransitionConstants;
use App\Enums\CommandEnum;
use App\Repositories\RequestRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class User extends Model
{
    protected $fillable = [
        'tg_user_id',
        'tg_chat_id',
        'username',
        'first_name',
        'last_name',
        'referrer_link'
    ];

    public function states()
    {
        return $this->belongsToMany(State::class, 'user_states');
    }

    public function flows()
    {
        return $this->hasMany(UserFlow::class, 'user_id');
    }

    public function referredUsers()
    {
        return $this->hasMany(UserReferral::class, 'user_id');
    }

    public function newsletters()
    {
        return $this->hasMany(Newsletter::class, 'user_id');
    }

    public static function getByRequestRepository(RequestRepository $repository): ?User
    {
        return User::where('tg_user_id', $repository->convertToUser()->getId())->first();
    }

    public static function createFromRequestRepository(RequestRepository $repository): User
    {
        $userDto = $repository->convertToUser();
        $chatDto = $repository->convertToChat();

        return self::create([
            'tg_user_id' => $userDto->getId(),
            'tg_chat_id' => $chatDto->getId(),
            'username' => $userDto->getUsername(),
            'first_name' => $userDto->getFirstName(),
            'last_name' => $userDto->getLastName(),
            'referrer_link' => Str::random(40)
        ]);
    }

    public static function getOrCreate(RequestRepository $repository): User
    {
        if ($user = self::getByRequestRepository($repository)) {
            return $user;
        }

        $user = self::createFromRequestRepository($repository);

        // Check if the user has referral link
        $message = $repository->convertToMessage()->getText();
        $user->addReferredUser($message);

        return $user;
    }

    public function getOpenedFlow(): ?UserFlow
    {
        return $this->flows->where('is_completed', 0)->first();
    }

    public function getLastFlow(): ?UserFlow
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
        if ($currentState = $this->states->first()) {
            return $currentState;
        }

        $startState = State::where('code', StateConstants::START)->first();
        $this->states()->attach($startState->id);

        return $startState;
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
     * @param Request $request
     * @return void
     */
    public function changeState(Request $request)
    {
        $requestRepository = new RequestRepository($request);
        $messageDto = $requestRepository->convertToMessage();
        $message = $messageDto->getText();

        switch ($message) {
            case CommandEnum::START->value:
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
                    // TODO: Make handler about unexpected text
                    if (
                        $previousState->code !== StateConstants::THEME_REQUEST &&
                        !in_array($message, $previousState->prepareCallbackItems($this))
                    ) {
                        Log::debug('Unexpected text'); return;
                    }

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
     * Add referred user if followed by referral link
     *
     * @param string $message
     * @return void
     */
    public function addReferredUser(string $message): void
    {
        if (str_starts_with($message, CommandEnum::START->value) && str_contains($message, ' ')) {
            $messageData = explode(' ', $message);
            $referralCode = $messageData[1];
            $parentUser = User::where('referrer_link', $referralCode)->first();

            $isUserReferred = UserReferral::where('referred_user_id', $this->id)->first();

            if ($parentUser && $parentUser->id !== $this->id && !$isUserReferred) {
                UserReferral::create([
                    'user_id' => $parentUser->id,
                    'referred_user_id' => $this->id,
                ]);
            }
        }
    }
}
