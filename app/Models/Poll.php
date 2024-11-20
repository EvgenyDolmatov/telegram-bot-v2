<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'tg_message_id',
        'question',
        'is_anonymous',
        'allows_multiple_answers',
        'type',
        'correct_option_id'
    ];

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }
}
