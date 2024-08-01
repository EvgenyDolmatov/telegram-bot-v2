<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['code', 'title', 'has_child', 'parent_id', 'sector_id', 'example'];
    public $timestamps = false;

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function hasChild(): bool
    {
        return $this->has_child;
    }
}
