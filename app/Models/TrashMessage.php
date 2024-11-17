<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrashMessage extends Model
{
    protected $fillable = ['chat_id', 'message_id', 'is_trash'];

    public static function add(int $chatId, int $messageId, bool $isTrash = false): TrashMessage
    {
        $trashMessage = new static();
        $trashMessage->chat_id = $chatId;
        $trashMessage->message_id = $messageId;
        $trashMessage->is_trash = $isTrash;
        $trashMessage->save();

        return $trashMessage;
    }
}
