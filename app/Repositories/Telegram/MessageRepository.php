<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\MessageDto;
use App\Exceptions\ResponseException;

final readonly class MessageRepository extends AbstractRepository
{
    /**
     * @throws ResponseException
     */
    public function getDto(): MessageDto
    {
        return parent::getMessageDto($this->request->all()['message']);
    }
}
