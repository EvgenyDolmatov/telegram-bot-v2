<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\MessageTextDto;

class MessageTextRepository implements RepositoryInterface
{
    public function createDto(?array $data = null): MessageTextDto
    {
        return new MessageTextDto();
    }
}
