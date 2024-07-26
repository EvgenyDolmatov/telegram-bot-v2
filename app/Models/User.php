<?php

namespace App\Models;

use App\Constants\StepConstants;
use App\Dto\UserDto;
use App\Entities\Message\UserEntity;
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
        if ($this->states->contains($state->id)) {
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
}
