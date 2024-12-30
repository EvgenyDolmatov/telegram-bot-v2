<?php

namespace App\Repositories\Telegram\Response;

use App\Dto\Telegram\MessageDto;
use App\Repositories\Telegram\Message\MessagePhotoRepository;
use App\Repositories\Telegram\Message\MessagePollRepository;
use App\Repositories\Telegram\Message\MessageTextRepository;
use Exception;

class MessageRepository extends AbstractRepository
{
    /**
     * @throws Exception
     */
    public function createDto(?array $data = null): MessageDto
    {
        return $this->defineRepository($data)->createDto();
    }

    /**
     * @throws Exception
     */
    public function defineRepository(
        ?array $data = null
    ): MessagePhotoRepository|MessageTextRepository|MessagePollRepository {
        $payload = $data ?? $this->payload['result'];

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
