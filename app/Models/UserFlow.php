<?php

namespace App\Models;

use App\Enums\Callback\PollEnum;
use App\Enums\StateEnum;
use Illuminate\Database\Eloquent\Model;

class UserFlow extends Model
{
    protected $fillable = ['user_id', 'flow', 'is_completed'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFlowData(): array
    {
        return json_decode($this->flow, true);
    }

    public function isQuiz(): bool
    {
        $flowData = $this->getFlowData();

        return
            isset($flowData[StateEnum::PollTypeChoice->value])
            && $flowData[StateEnum::PollTypeChoice->value] === PollEnum::TypeQuiz->value;
    }
}
