<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['code'];
    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_states');
    }
}
