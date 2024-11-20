<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
