<?php

namespace App\Repositories;

use App\Dto\ChatDto;
use App\Dto\MessageDto;
use App\Dto\UserDto;
use Illuminate\Http\Client\Response;

readonly class ResponseRepository
{
    public function __construct(
        private Response $response
    ) {}

    public function getData(): array
    {
        $payload = array();
        $data = json_decode($this->response, true);

        if(isset($data['result'])) {
            $payload['id'] = $data['result']['message_id'];
            $payload['date'] = $data['result']['date'];

            $payload['from']['id'] = $data['result']['from']['id'];
            $payload['from']['is_bot'] = $data['result']['from']['is_bot'];
            $payload['from']['first_name'] = $data['result']['from']['first_name'] ?? null;
            $payload['from']['last_name'] = $data['result']['from']['last_name'] ?? null;
            $payload['from']['username'] = $data['result']['from']['username'] ?? null;

            $payload['chat']['id'] = $data['result']['chat']['id'];
            $payload['chat']['first_name'] = $data['result']['chat']['first_name'] ?? null;
            $payload['chat']['last_name'] = $data['result']['chat']['last_name'] ?? null;
            $payload['chat']['username'] = $data['result']['chat']['username'] ?? null;
            $payload['chat']['type'] = $data['result']['chat']['type'];
        }

        return $payload;
    }

    public function convertToMessage(): MessageDto
    {
        $payload = $this->getData();

        return new MessageDto($payload['id'], $payload['text'] ?? null);
    }

    public function convertToChat(): ChatDto
    {
        $payload = $this->getData();

        return new ChatDto(
            $payload['chat']['id'],
            $payload['chat']['username'],
            $payload['chat']['type'],
            $payload['chat']['first_name'],
            $payload['chat']['last_name']
        );
    }

    public function convertToUser(): UserDto
    {
        $payload = $this->getData();

        return new UserDto(
            $payload['from']['id'],
            $payload['from']['username'],
            $payload['from']['is_bot'],
            $payload['from']['first_name'],
            $payload['from']['last_name']
        );
    }
}
