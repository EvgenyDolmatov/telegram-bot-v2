<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreparedPoll extends Model
{
    use HasFactory;

    protected $table = 'prepared_polls';
    protected $fillable = ['user_id', 'tg_message_id', 'poll_ids'];
}
