<?php

namespace App\Repositories\Telegram\Response;

use App\Dto\Telegram\ChannelDto;
use App\Dto\Telegram\GroupDto;
use App\Repositories\Telegram\Community\ChannelRepository;
use App\Repositories\Telegram\Community\GroupRepository;
use Exception;

class CommunityRepository extends AbstractRepository
{
    /**
     * @throws Exception
     */
    public function createDto(?array $data = null): ChannelDto|GroupDto
    {
        return $this->defineRepository($data)->createDto();
    }

    /**
     * @throws Exception
     */
    public function defineRepository(?array $data = null): ChannelRepository|GroupRepository
    {
        $payload = $data ?? $this->payload['result'];

        if (array_key_exists('type', $payload)) {
            if ($payload['type'] === 'supergroup') {
                return new GroupRepository($payload);
            }

            if ($payload['type'] === 'channel') {
                return new ChannelRepository($payload);
            }
        }

        throw new Exception('Unavailable to create message DTO in CommunityRepository.');
    }
}
