<?php

namespace App\Models;

use App\Enums\CallbackEnum;
use App\Enums\CommandEnum;
use App\Enums\StateEnum;
use App\Repositories\RequestRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function states(): BelongsToMany
    {
        return $this->belongsToMany(State::class, 'user_states');
    }

    public function flows(): HasMany
    {
        return $this->hasMany(UserFlow::class, 'user_id');
    }

    public function referredUsers(): HasMany
    {
        return $this->hasMany(UserReferral::class, 'user_id');
    }

    public function newsletters(): HasMany
    {
        return $this->hasMany(Newsletter::class, 'user_id');
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class, 'user_id');
    }

    public function preparedPolls(): HasMany
    {
        return $this->hasMany(PreparedPoll::class, 'user_id');
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'user_id');
    }

    public static function getByRequestRepository(RequestRepository $repository): ?User
    {
        $telegramUserId = $repository->getDto()->getFrom()->getId();

        return User::where('tg_user_id', $telegramUserId)->first();
    }

    /**
     * @throws \Exception
     */
    public static function createFromRequestRepository(RequestRepository $repository): User
    {
        $messageDto = $repository->getDto();
        $from = $repository->getDto()->getFrom();

        return self::create([
            'tg_user_id' => $from->getId(),
            'tg_chat_id' => $messageDto->getChat()->getId(),
            'username' => $from->getUsername(),
            'first_name' => $from->getFirstName(),
            'last_name' => $from->getLastName(),
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
        $user->addReferredUser($repository->getDto()->getText());

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
        if ($currentState = $this->states()->first()) {
            return $currentState;
        }

        $startState = State::where('code', StateEnum::START->value)->first();
        $this->states()->attach($startState->id);

        return $startState;
    }

    public function hasAnyState(): bool
    {
        return $this->states()->count();
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

    /**
     * Update user state
     */
    public function updateStateByCode(string $code): void
    {
        $state = State::where('code', $code)->first();

        if ($state) {
            $this->states()->detach();
            $this->states()->attach($state->id);
        }

    }

    public function updateFlow(StateEnum $state, ?string $value = null, bool $isCompleted = false): UserFlow
    {
        if ($openedFlow = $this->getOpenedFlow()) {
            $flowData = $this->updateFlowData(
                data: json_decode($openedFlow->flow, true),
                state: $state,
                value: $value
            );

            $openedFlow->update([
                'flow' => json_encode($flowData),
                'is_completed' => $isCompleted
            ]);

            return $openedFlow;
        }

        return UserFlow::create([
            'user_id' => $this->id,
            'flow' => json_encode([$state->value => $value]),
            'is_completed' => $isCompleted,
        ]);
    }

    public function resetFlow(): void
    {
        if ($openedFlow = $this->getOpenedFlow()){
            $openedFlow->delete();
        }
    }

    private function updateFlowData(array $data, StateEnum $state, ?string $value = null): array
    {
        if ($value) {
            if ($value === CallbackEnum::BACK->value) {
                if (array_key_exists($state->backState()->value, $data)) {
                    unset($data[$state->backState()->value]);
                }

                return $data;
            }

            $data[$state->value] = $value;
        }

        return $data;
    }
}
