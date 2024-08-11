<?php

namespace App\Models;

use App\Constants\CallbackConstants;
use App\Constants\StateConstants;
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
            isset($flowData[StateConstants::ANON_CHOICE])
            && $flowData[StateConstants::ANON_CHOICE] === CallbackConstants::IS_ANON;
    }

    public function isQuiz(): bool
    {
        $flowData = $this->getFlowData();

        return
            isset($flowData[StateConstants::TYPE_CHOICE])
            && $flowData[StateConstants::TYPE_CHOICE] === CallbackConstants::TYPE_QUIZ;
    }
}
