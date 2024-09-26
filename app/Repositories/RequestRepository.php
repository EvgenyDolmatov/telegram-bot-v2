<?php

namespace App\Repositories;

use App\Dto\ChatDto;
use App\Dto\Message\PhotoDto;
use App\Dto\MessageDto;
use App\Dto\UserDto;
use Illuminate\Http\Request;

readonly class RequestRepository
{
    public function __construct(
        private Request $request
    ) {}

    private function hasPhoto(): bool
    {
        $data = $this->request->all();

        return isset($data['message']['photo']);
    }

    public function getData(): array
    {
        $payload = array();
        $data = $this->request->all();

        // Response from user request after send simple message
        if (isset($data['message'])) {
            $payload['id'] = $data['message']['message_id'];
            $payload['date'] = $data['message']['date'];
            $payload['text'] = $data['message']['text'] ?? null;

            if ($this->hasPhoto()) {
                $payload['text'] = $data['message']['caption'] ?? null;

                $fullSizePhotoIndex = $data['message']['photo'] ? count($data['message']['photo']) - 1 : null;
                if ($fullSizePhotoIndex) {
                    $payload['photo']['file_id'] = $data['message']['photo'][$fullSizePhotoIndex]['file_id'];
                    $payload['photo']['file_unique_id'] = $data['message']['photo'][$fullSizePhotoIndex]['file_unique_id'];
                    $payload['photo']['file_size'] = $data['message']['photo'][$fullSizePhotoIndex]['file_size'];
                    $payload['photo']['width'] = $data['message']['photo'][$fullSizePhotoIndex]['width'];
                    $payload['photo']['height'] = $data['message']['photo'][$fullSizePhotoIndex]['height'];
                }
            }

            $payload['from']['id'] = $data['message']['from']['id'];
            $payload['from']['is_bot'] = $data['message']['from']['is_bot'];
            $payload['from']['first_name'] = $data['message']['from']['first_name'] ?? null;
            $payload['from']['last_name'] = $data['message']['from']['last_name'] ?? null;
            $payload['from']['username'] = $data['message']['from']['username'] ?? null;

            $payload['chat']['id'] = $data['message']['chat']['id'];
            $payload['chat']['first_name'] = $data['message']['chat']['first_name'] ?? null;
            $payload['chat']['last_name'] = $data['message']['chat']['last_name'] ?? null;
            $payload['chat']['username'] = $data['message']['chat']['username'] ?? null;
            $payload['chat']['type'] = $data['message']['chat']['type'];
        }

        // Response from user request after click by button
        if(isset($data['callback_query'])) {
            $payload['id'] = $data['callback_query']['message']['message_id'];
            $payload['date'] = $data['callback_query']['message']['date'];
            $payload['text'] = $data['callback_query']['data'];

            $payload['from']['id'] = $data['callback_query']['from']['id'];
            $payload['from']['is_bot'] = $data['callback_query']['from']['is_bot'];
            $payload['from']['first_name'] = $data['callback_query']['from']['first_name'] ?? null;
            $payload['from']['last_name'] = $data['callback_query']['from']['last_name'] ?? null;
            $payload['from']['username'] = $data['callback_query']['from']['username'] ?? null;

            $payload['chat']['id'] = $data['callback_query']['message']['chat']['id'];
            $payload['chat']['first_name'] = $data['callback_query']['message']['chat']['first_name'] ?? null;
            $payload['chat']['last_name'] = $data['callback_query']['message']['chat']['last_name'] ?? null;
            $payload['chat']['username'] = $data['callback_query']['message']['chat']['username'] ?? null;
            $payload['chat']['type'] = $data['callback_query']['message']['chat']['type'];
        }

        return $payload;
    }

    public function convertToMessage(): MessageDto
    {
        $payload = $this->getData();

        $photo = isset($payload['photo']) ? new PhotoDto(
            $payload['photo']['file_id'],
            $payload['photo']['file_unique_id'],
            $payload['photo']['file_size'],
            $payload['photo']['width'],
            $payload['photo']['height'],
        ) : null;

        return new MessageDto(
            id: $payload['id'],
            text: $payload['text'] ?? null,
            photo: $photo
        );
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
