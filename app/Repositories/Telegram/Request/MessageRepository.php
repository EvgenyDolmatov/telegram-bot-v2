<?php

namespace App\Repositories\Telegram\Request;

use App\Dto\Telegram\MessagePhotoDto;
use App\Dto\Telegram\MessagePollDto;
use App\Dto\Telegram\MessageTextDto;
use App\Repositories\Telegram\Message\MessagePhotoRepository;
use App\Repositories\Telegram\Message\MessagePollRepository;
use App\Repositories\Telegram\Message\MessageTextRepository;
use Exception;

class MessageRepository extends AbstractRepository
{
    /**
     * @throws Exception
     */
    public function createDto(?array $data = null): MessageTextDto|MessagePhotoDto|MessagePollDto
    {
        return $this->defineRepository($data)->createDto();
    }

    /**
     * @throws Exception
     */
    public function defineRepository(
        ?array $data = null
    ): MessagePhotoRepository|MessageTextRepository|MessagePollRepository {
        $payload = $data ?? $this->payload;

        if (array_key_exists('text', $payload)) {
            return new MessageTextRepository($payload);
        }

        if (array_key_exists('photo', $payload)) {
            return new MessagePhotoRepository($payload);
        }

        if (array_key_exists('poll', $payload)) {
            return new MessagePollRepository($payload);
        }

        throw new Exception('Unavailable to create message DTO in CallbackRepository.');
    }
}
