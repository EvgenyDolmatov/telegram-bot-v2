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
                $sectorCode = $user->getOpenedFlow()->getValueByKey(StateConstants::SECTOR_CHOICE);
                Log::debug('as: ' . $sectorCode);
                return [];
//
//                $sector = Sector::where('code', $sectorCode)->first();
//                $subjects = Subject::where('sector_id', $sector->id)->where('parent_id', null)->get();
//
//                return array_map(
//                    fn($subject) => [
//                        ButtonKeyConstants::TEXT => $subject['title'],
//                        ButtonKeyConstants::CALLBACK => $subject['code'],
//                    ],
//                    $subjects->toArray()
//                );
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
