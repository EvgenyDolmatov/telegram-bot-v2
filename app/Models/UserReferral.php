<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReferral extends Model
{
    protected $table = 'user_referrals';
    protected $fillable = ['user_id', 'referred_user_id'];
}
