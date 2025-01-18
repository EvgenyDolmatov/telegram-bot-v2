<?php

namespace App\Models;

use App\Enums\CallbackEnum;
use App\Enums\CommandEnum;
use App\Enums\StateEnum;
use App\Exceptions\ResponseException;
use App\Repositories\Telegram\Request\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'referrer_link',
        'role_id',
        'state'
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function pollGroups(): HasMany
    {
        return $this->hasMany(PollGroup::class, 'user_id');
    }

    public static function getByRequestRepository(RepositoryInterface $repository): ?User
    {
        $telegramUserId = $repository->createDto()->getFrom()->getId();

        return User::where('tg_user_id', $telegramUserId)->first();
    }

    public function isAdmin(): bool
    {
        $adminRole = Role::where('code', 'admin')->first();

        return $this->role_id === $adminRole->id;
    }

    /**
     * @throws \Exception
     */
    public static function createFromRequestRepository(
        RepositoryInterface $repository,
        ?string $roleCode = 'subscriber'
    ): User {
        $messageDto = $repository->createDto();
        $from = $messageDto->getFrom();

        return self::create([
            'tg_user_id' => $from->getId(),
            'tg_chat_id' => $messageDto->getChat()->getId(),
            'username' => $from->getUsername(),
            'first_name' => $from->getFirstName(),
            'last_name' => $from->getLastName(),
            'referrer_link' => Str::random(40),
            'role_id' => Role::where('code', $roleCode)->first()->id
        ]);
    }

    /**
     * @throws ResponseException
     */
    public static function getOrCreate(RepositoryInterface $repository, ?string $roleCode = 'subscriber'): User
    {
        if ($user = self::getByRequestRepository($repository)) {
            return $user;
        }

        $user = self::createFromRequestRepository($repository, $roleCode);

        // Check if the user has referral link
        $user->addReferredUser($repository->createDto()->getText());

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

    public function getCurrentState(): string
    {
        if ($currentState = $this->state) {
            return $currentState;
        }

        return StateEnum::Start->value;
    }

    public function getPreparedPoll(): ?PreparedPoll
    {
        return $this->preparedPolls->last();
    }

    public function deletePreparedPoll(): void
    {
        if ($preparedPoll = $this->getPreparedPoll()) {
            $preparedPoll->delete();
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
        if (str_starts_with($message, CommandEnum::Start->value) && str_contains($message, ' ')) {
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
            if ($value === CallbackEnum::Back->value) {
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
