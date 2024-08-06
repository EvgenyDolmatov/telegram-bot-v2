<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFlow extends Model
{
    protected $fillable = ['user_id', 'flow', 'is_completed'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getValueByKey(string $key): mixed
    {
        $flowData = json_decode($this->flow, true);
        return $flowData[$key] ?? null;
    }
}
