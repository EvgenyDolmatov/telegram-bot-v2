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
    ) {
    }

    public function toArray(): array
    {
        return json_decode($this->response, true);
    }

    public function getData(): array
    {
        $payload = array();
        $body = $this->toArray();

        if(isset($body['result'])) {
            $payload['id'] = $body['result']['message_id'];
            $payload['date'] = $body['result']['date'];
//            $payload['text'] = $body['result']['text'];

            $payload['from']['id'] = $body['result']['from']['id'];
            $payload['from']['is_bot'] = $body['result']['from']['is_bot'];
            $payload['from']['first_name'] = $body['result']['from']['first_name'] ?? null;
            $payload['from']['last_name'] = $body['result']['from']['last_name'] ?? null;
            $payload['from']['username'] = $body['result']['from']['username'];

            $payload['chat']['id'] = $body['result']['chat']['id'];
            $payload['chat']['first_name'] = $body['result']['chat']['first_name'] ?? null;
            $payload['chat']['last_name'] = $body['result']['chat']['last_name'] ?? null;
            $payload['chat']['username'] = $body['result']['chat']['username'];
            $payload['chat']['type'] = $body['result']['chat']['type'];
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
