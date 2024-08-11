<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiRequest extends Model
{
    protected $fillable = [
        'tg_chat_id',
        'user_flow_id',
        'ai_survey',
        'usage_prompt_tokens',
        'usage_completion_tokens',
        'usage_total_tokens'
    ];
}
