<?php

namespace App\Models;

use App\Constants\StateConstants;
use App\Constants\TransitionConstants;
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
    public function prepareButtons(User $user, bool $hasBackButton = false): array
    {
        switch ($this->code) {
            case StateConstants::SECTOR_CHOICE:
                $buttons = array_map(
                    fn($sector) => new ButtonDto($sector['code'], $sector['title']),
                    Sector::all()->toArray()
                );
                break;
            case StateConstants::SUBJECT_CHOICE:
                $flow = $user->getFlowData();

                if (isset($flow[StateConstants::SUBJECT_CHOICE])) {
                    $parentSubject = Subject::where('code', $flow[StateConstants::SUBJECT_CHOICE])->first();
                }

                if (isset($parentSubject) && $parentSubject->has_child) {
                    $subjects = Subject::where('parent_id', $parentSubject->id)->get();
                } else {
                    $sector = Sector::where('code', $flow['sector_choice'])->first();
                    $subjects = Subject::where('sector_id', $sector->id)->where('parent_id', null)->get();
                }

                $buttons = array_map(
                    fn($subject) => new ButtonDto($subject['code'], $subject['title']),
                    $subjects->toArray()
                );
                break;
            default:
                $buttons = $this->buttons()->get()->count()
                    ? array_map(
                        fn($button) => new ButtonDto($button['callback'], $button['text']),
                        $this->buttons()->get()->toArray()
                    )
                    : [];
                break;
        }

        if ($hasBackButton) {
            $buttons[] = new ButtonDto(TransitionConstants::BACK, 'Назад');
        }

        return $buttons;
    }

    public function prepareCallbackItems(User $user): array
    {
        return array_map(
            fn($button) => $button->getCallbackData(),
            $this->prepareButtons($user)
        );
    }
}
