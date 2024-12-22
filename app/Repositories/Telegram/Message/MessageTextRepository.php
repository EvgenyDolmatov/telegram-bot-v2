<?php

namespace App\Repositories\Telegram\Message;

use App\Dto\Telegram\MessageTextDto;
use App\Repositories\Telegram\Request\MessageRepository;

class MessageTextRepository extends MessageRepository
{
    public function createDto(?array $data = null): MessageTextDto
    {
        $data = $data ?? $this->payload;

        return (new MessageTextDto())
            ->setId($data['message_id'])
            ->setFrom($this->getFromDto($data['from']))
            ->setChat($this->getChatDto($data['chat']))
            ->setDate($data['date'])
            ->setText($data['text']);
    }
}
