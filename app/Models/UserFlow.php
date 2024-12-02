<?php

namespace App\Models;

use App\Enums\PollEnum;
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

    public function isAnonymous(): bool
    {
        $flowData = $this->getFlowData();

        return
            isset($flowData[StateEnum::POLL_ANONYMITY_CHOICE->value])
            && $flowData[StateEnum::POLL_ANONYMITY_CHOICE->value] === PollEnum::IS_ANON->value;
    }

    public function isQuiz(): bool
    {
        $flowData = $this->getFlowData();

        return
            isset($flowData[StateEnum::POLL_TYPE_CHOICE->value])
            && $flowData[StateEnum::POLL_TYPE_CHOICE->value] === PollEnum::TYPE_QUIZ->value;
    }
}
