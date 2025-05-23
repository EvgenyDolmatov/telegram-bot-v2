<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'poll_ids', 'title', 'description', 'time_limit', 'channel', 'is_completed'];
}
