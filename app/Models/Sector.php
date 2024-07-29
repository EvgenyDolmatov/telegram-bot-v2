<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = ['code', 'title'];
    public $timestamps = false;

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
