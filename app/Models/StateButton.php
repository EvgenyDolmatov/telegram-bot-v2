<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StateButton extends Model
{
    protected $fillable = ['state_id', 'text', 'callback'];
    public $timestamps = false;
}
