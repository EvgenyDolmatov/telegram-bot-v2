<?php

namespace App\Models;

use App\Constants\ButtonKeyConstants;
use App\Constants\StateConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
     * @return array
     */
    public function prepareButtons(User $user): array
    {
        switch ($this->code) {
            case StateConstants::SECTOR_CHOICE:
                return array_map(
                    fn($sector) => [
                        ButtonKeyConstants::TEXT => $sector['title'],
                        ButtonKeyConstants::CALLBACK => $sector['code'],
                    ],
                    Sector::all()->toArray()
                );
            case StateConstants::SUBJECT_CHOICE:
                Log::debug('123123');
                $flow = $user->getFlowData();
                $sector = Sector::where('code', $flow['sector_choice'])->first();
                return array_map(
                    fn($subject) => [
                        ButtonKeyConstants::TEXT => $subject['title'],
                        ButtonKeyConstants::CALLBACK => $subject['code'],
                    ],
                    Subject::where('sector_id', $sector->id)->where('parent_id', null)->get()->toArray()
                );
            default:
                return array_map(
                    fn($button) => [
                        ButtonKeyConstants::TEXT => $button['text'],
                        ButtonKeyConstants::CALLBACK => $button['callback'],
                    ],
                    $this->buttons()->get()->toArray()
                );
        }
    }


    public function prepareCallbackItems(User $user): array
    {
        return array_map(
            fn($button) => $button['callback_data'],
            $this->prepareButtons($user)
        );
    }
}
