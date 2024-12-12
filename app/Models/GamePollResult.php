<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamePollResult extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'poll_id', 'answer_index', 'time_spent', 'score'];
}
