<?php

namespace App\Repositories;

use App\Dto\Message\ChatDto;
use App\Dto\Message\FromDto;
use App\Dto\Message\PhotoDto;
use App\Dto\MessageDto;
use App\Exceptions\ResponseException;
use Illuminate\Http\Request;
use Throwable;

readonly class RequestRepository
{
    public function __construct(
        private Request $request
    ) {
    }

    /**
     * @throws ResponseException
     */
    public function getDto(): MessageDto
    {
        $payload = $this->request->all();

        if (array_key_exists('callback_query', $payload)) {
            return $this->getDtoByCallback();
        }

        if (array_key_exists('message', $payload)) {
            return $this->getDtoByMessage();
        }

        throw new ResponseException();
    }

    /**
     * @throws ResponseException
     */
    private function getDtoByMessage(): MessageDto
    {
        try {
            $payload = $this->request->all();
            $data = $payload['message'];

            if (array_key_exists('photo', $data)) {
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
        } catch (Throwable $e) {
            throw new ResponseException($e->getMessage());
        }
    }

    /**
     * @throws ResponseException
     */
    private function getDtoByCallback(): MessageDto
    {
        try {
            $payload = $this->request->all();
            $data = $payload['callback_query'];

            return (new MessageDto())
                ->setId($data['message']['message_id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setChat($this->getChatDto($data['message']['chat']))
                ->setDate($data['message']['date'])
                ->setText($data['data']);
        } catch (Throwable $e) {
            throw new ResponseException($e->getMessage());
        }
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
}
