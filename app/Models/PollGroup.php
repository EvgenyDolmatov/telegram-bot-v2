<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollGroup extends Model
{
    use HasFactory;

    protected $table = 'poll_groups';

    protected $fillable = ['user_id', 'poll_ids', 'is_closed'];
}
