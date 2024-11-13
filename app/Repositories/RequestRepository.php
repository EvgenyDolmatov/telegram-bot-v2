<?php

namespace App\Repositories;

use App\Dto\Message\ChatDto;
use App\Dto\Message\FromDto;
use App\Dto\Message\PhotoDto;
use App\Dto\MessageDto;
use Illuminate\Http\Request;

readonly class RequestRepository
{
    public function __construct(
        private Request $request
    ) {
    }

    /**
     * @throws \Exception
     */
    public function getDto(): MessageDto
    {
        $payload = $this->request->all();

        if (isset($payload['callback_query'])) {
            return $this->getDtoByCallback();
        }

        if (isset($payload['message'])) {
            return $this->getDtoByMessage();
        }

        throw new \Exception("Data is invalid. Array must contain 'message' or 'callback_query'.");
    }

    private function getDtoByMessage(): MessageDto
    {
        $payload = $this->request->all();
        $data = $payload['message'];

        if (isset($data['photo'])) {
            return (new MessageDto())
                ->setId($data['message_id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setChat($this->getChatDto($data['chat']))
                ->setPhoto($this->getImages($data['photo']))
                ->setDate($data['date'])
                ->setText($data['caption']);
        }

        return (new MessageDto())
            ->setId($data['message_id'])
            ->setFrom($this->getFromDto($data['from']))
            ->setChat($this->getChatDto($data['chat']))
            ->setDate($data['date'])
            ->setText($data['text']);
    }

    private function getDtoByCallback(): MessageDto
    {
        $payload = $this->request->all();
        $data = $payload['callback_query'];

        return (new MessageDto())
            ->setId($data['message']['message_id'])
            ->setFrom($this->getFromDto($data['from']))
            ->setChat($this->getChatDto($data['message']['chat']))
            ->setDate($data['message']['date'])
            ->setText($data['data']);
    }

    private function getChatDto(array $data): ChatDto
    {
        return (new ChatDto())
            ->setId($data['id'])
            ->setUsername($data['username'] ?? null)
            ->setFirstName($data['first_name'] ?? null)
            ->setLastName($data['last_name'] ?? null)
            ->setType($data['type']);
    }

    private function getFromDto(array $data): FromDto
    {
        return (new FromDto())
            ->setId($data['id'])
            ->setIsBot($data['is_bot'])
            ->setUsername($data['username'] ?? null)
            ->setFirstName($data['first_name'] ?? null)
            ->setLastName($data['last_name'] ?? null)
            ->setLanguageCode($data['language_code'] ?? null);
    }

    private function getPhotoDto(array $image): PhotoDto
    {
        return (new PhotoDto())
            ->setFileId($image['file_id'])
            ->setFileUniqueId($image['file_unique_id'])
            ->setFileSize($image['file_size'])
            ->setWidth($image['width'])
            ->setHeight($image['height']);
    }

    private function getImages(array $data): array
    {
        $images = [];
        foreach ($data as $image) {
            $images[] = $this->getPhotoDto($image);
        }

        return $images;
    }











//    private function hasPhoto(): bool
//    {
//        $data = $this->request->all();
//
//        return isset($data['message']['photo']);
//    }
//
//    public function getData(): array
//    {
//        $payload = array();
//        $data = $this->request->all();
//
//        // Response from user request after send simple message
//        if (isset($data['message'])) {
//            $payload['id'] = $data['message']['message_id'];
//            $payload['date'] = $data['message']['date'];
//            $payload['text'] = $data['message']['text'] ?? null;
//
//            if ($this->hasPhoto()) {
//                $payload['text'] = $data['message']['caption'] ?? null;
//
//                $fullSizePhotoIndex = $data['message']['photo'] ? count($data['message']['photo']) - 1 : null;
//                if ($fullSizePhotoIndex) {
//                    $payload['photo']['file_id'] = $data['message']['photo'][$fullSizePhotoIndex]['file_id'];
//                    $payload['photo']['file_unique_id'] = $data['message']['photo'][$fullSizePhotoIndex]['file_unique_id'];
//                    $payload['photo']['file_size'] = $data['message']['photo'][$fullSizePhotoIndex]['file_size'];
//                    $payload['photo']['width'] = $data['message']['photo'][$fullSizePhotoIndex]['width'];
//                    $payload['photo']['height'] = $data['message']['photo'][$fullSizePhotoIndex]['height'];
//                }
//            }
//
//            $payload['from']['id'] = $data['message']['from']['id'];
//            $payload['from']['is_bot'] = $data['message']['from']['is_bot'];
//            $payload['from']['first_name'] = $data['message']['from']['first_name'] ?? null;
//            $payload['from']['last_name'] = $data['message']['from']['last_name'] ?? null;
//            $payload['from']['username'] = $data['message']['from']['username'] ?? null;
//
//            $payload['chat']['id'] = $data['message']['chat']['id'];
//            $payload['chat']['first_name'] = $data['message']['chat']['first_name'] ?? null;
//            $payload['chat']['last_name'] = $data['message']['chat']['last_name'] ?? null;
//            $payload['chat']['username'] = $data['message']['chat']['username'] ?? null;
//            $payload['chat']['type'] = $data['message']['chat']['type'];
//        }
//
//        // Response from user request after click by button
//        if(isset($data['callback_query'])) {
//            $payload['id'] = $data['callback_query']['message']['message_id'];
//            $payload['date'] = $data['callback_query']['message']['date'];
//            $payload['text'] = $data['callback_query']['data'];
//
//            $payload['from']['id'] = $data['callback_query']['from']['id'];
//            $payload['from']['is_bot'] = $data['callback_query']['from']['is_bot'];
//            $payload['from']['first_name'] = $data['callback_query']['from']['first_name'] ?? null;
//            $payload['from']['last_name'] = $data['callback_query']['from']['last_name'] ?? null;
//            $payload['from']['username'] = $data['callback_query']['from']['username'] ?? null;
//
//            $payload['chat']['id'] = $data['callback_query']['message']['chat']['id'];
//            $payload['chat']['first_name'] = $data['callback_query']['message']['chat']['first_name'] ?? null;
//            $payload['chat']['last_name'] = $data['callback_query']['message']['chat']['last_name'] ?? null;
//            $payload['chat']['username'] = $data['callback_query']['message']['chat']['username'] ?? null;
//            $payload['chat']['type'] = $data['callback_query']['message']['chat']['type'];
//        }
//
//        return $payload;
//    }
}
