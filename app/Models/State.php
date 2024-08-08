<?php

namespace App\Models;

use App\Constants\StateConstants;
use App\Dto\ButtonDto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['code'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_states');
    }

    public function buttons()
    {
        return $this->hasMany(StateButton::class);
    }

    /**
     * @param User $user
     * @return array
     */
    public function prepareButtons(User $user): array
    {
        switch ($this->code) {
            case StateConstants::SECTOR_CHOICE:
                return array_map(
                    fn($subject) => new ButtonDto($subject['code'], $subject['title']),
                    Sector::all()->toArray()
                );
            case StateConstants::SUBJECT_CHOICE:
                $flow = $user->getFlowData();
                $sector = Sector::where('code', $flow['sector_choice'])->first();

                return array_map(
                    fn($subject) => new ButtonDto($subject['code'], $subject['title']),
                    Subject::where('sector_id', $sector->id)->where('parent_id', null)->get()->toArray()
                );
            default:
                return array_map(
                    fn($button) => new ButtonDto($button['callback'], $button['text']),
                    $this->buttons()->get()->toArray()
                );
        }
    }

    public function prepareCallbackItems(User $user): array
    {
        return array_map(
            fn($button) => $button->getCallbackData(),
            $this->prepareButtons($user)
        );
    }
}
