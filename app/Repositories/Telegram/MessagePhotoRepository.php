<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\MessagePhotoDto;

class MessagePhotoRepository implements RepositoryInterface
{
    public function createDto(?array $data = null): MessagePhotoDto
    {
        return new MessagePhotoDto();
    }
}
