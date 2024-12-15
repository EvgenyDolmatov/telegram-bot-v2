<?php

namespace App\Repositories\Telegram;

use App\Dto\Telegram\CallbackQueryDto;
use App\Dto\Telegram\MessagePhotoDto;
use App\Dto\Telegram\MessageTextDto;
use App\Exceptions\ResponseException;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class MessageRepository extends AbstractRepository
{
    /**
     * @throws \Exception
     */
    public function getDto(array $data = null): MessageTextDto|MessagePhotoDto
    {
        return parent::getMessageDto($data);
    }
}
