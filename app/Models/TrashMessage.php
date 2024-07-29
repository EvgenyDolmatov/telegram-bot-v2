<?php

namespace App\Models;

use App\Dto\ChatDto;
use App\Dto\MessageDto;
use Illuminate\Database\Eloquent\Model;

class TrashMessage extends Model
{
    protected $fillable = ['chat_id', 'message_id', 'is_trash'];

    public static function add(ChatDto $chat, MessageDto $message, bool $isTrash = false): TrashMessage
    {
        $trashMessage = new static();
        $trashMessage->chat_id = $chat->getId();
        $trashMessage->message_id = $message->getId();
        $trashMessage->is_trash = $isTrash;
        $trashMessage->save();

        return $trashMessage;
    }
}
