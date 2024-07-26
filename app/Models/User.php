<?php

namespace App\Models;

use App\Constants\StepConstants;
use App\Entities\Message\UserEntity;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'username',
        'first_name',
        'last_name',
        'current_step',
    ];
}
