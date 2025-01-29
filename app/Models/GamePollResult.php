<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamePollResult extends Model
{
    use HasFactory;

    protected $table = 'game_poll_results';
    protected $fillable = ['user_id', 'game_id', 'poll_id', 'answer', 'time', 'points'];
}
