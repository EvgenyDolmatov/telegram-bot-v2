<?php

namespace App\Repositories;

use App\Dto\ChatDto;
use App\Dto\MessageDto;
use App\Dto\UserDto;
use Illuminate\Http\Request;

class RequestRepository
{
    private string $body;

    public function __construct(private readonly Request $request)
    {
        $this->body = json_encode($this->request->all());
    }

    public function toArray(): array
    {
        return json_decode($this->body, true);
    }

    public function getData(): array
    {
        $payload = array();
        $body = $this->toArray();

        // Response from user request after send simple message
        if (isset($body['message'])) {
            $payload['id'] = $body['message']['message_id'];
            $payload['date'] = $body['message']['date'];
            $payload['text'] = $body['message']['text'];

            $payload['from']['id'] = $body['message']['from']['id'];
            $payload['from']['is_bot'] = $body['message']['from']['is_bot'];
            $payload['from']['first_name'] = $body['message']['from']['first_name'] ?? null;
            $payload['from']['last_name'] = $body['message']['from']['last_name'] ?? null;
            $payload['from']['username'] = $body['message']['from']['username'];

            $payload['chat']['id'] = $body['message']['chat']['id'];
            $payload['chat']['first_name'] = $body['message']['chat']['first_name'] ?? null;
            $payload['chat']['last_name'] = $body['message']['chat']['last_name'] ?? null;
            $payload['chat']['username'] = $body['message']['chat']['username'];
            $payload['chat']['type'] = $body['message']['chat']['type'];
        }

        // Response from user request after click by button
        if(isset($body['callback_query'])) {
            $payload['id'] = $body['callback_query']['message']['message_id'];
            $payload['date'] = $body['callback_query']['message']['date'];
            $payload['text'] = $body['callback_query']['data'];

            $payload['from']['id'] = $body['callback_query']['from']['id'];
            $payload['from']['is_bot'] = $body['callback_query']['from']['is_bot'];
            $payload['from']['first_name'] = $body['callback_query']['from']['first_name'] ?? null;
            $payload['from']['last_name'] = $body['callback_query']['from']['last_name'] ?? null;
            $payload['from']['username'] = $body['callback_query']['from']['username'];

            $payload['chat']['id'] = $body['callback_query']['message']['chat']['id'];
            $payload['chat']['first_name'] = $body['callback_query']['message']['chat']['first_name'] ?? null;
            $payload['chat']['last_name'] = $body['callback_query']['message']['chat']['last_name'] ?? null;
            $payload['chat']['username'] = $body['callback_query']['message']['chat']['username'];
            $payload['chat']['type'] = $body['callback_query']['message']['chat']['type'];
        }

        return $payload;
    }

    public function convertToMessage(): MessageDto
    {
        $payload = $this->getData();

        return new MessageDto($payload['id'], $payload['text']);
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
