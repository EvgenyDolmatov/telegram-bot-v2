<?php

namespace App\Models;

use App\Constants\ButtonKeyConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['code'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_states');
    }

    public function buttons()
    {
        return $this->hasMany(StateButton::class);
    }

    /**
     * @return array
     */
    public function prepareButtons(): array
    {
        return array_map(fn($button) => [
            ButtonKeyConstants::TEXT => $button['text'],
            ButtonKeyConstants::CALLBACK => $button['callback'],
        ], $this->buttons()->get()->toArray());
    }
}
