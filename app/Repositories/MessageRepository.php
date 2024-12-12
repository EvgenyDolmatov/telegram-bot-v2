<?php

namespace App\Repositories;

use App\Dto\Message\ChatDto;
use App\Dto\Message\FromDto;
use App\Dto\Message\PhotoDto;
use App\Dto\MessageDto;

class MessageRepository extends AbstractRepository
{
    /**
     * @throws \Exception
     *
     * TODO: Переработать репозиторий получения данных, если response получаем из канала. Создать стратегию
     */
    public function getDto(): ?MessageDto
    {
        $data = json_decode($this->response, true)['result'];

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
            ->setFrom($this->getFromDto($data['from'] ?? null))
            ->setChat($this->getChatDto($data['chat']))
            ->setDate($data['date'])
            ->setText($data['text'] ?? null);
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

    private function getFromDto(?array $data = null): ?FromDto
    {
        return $data
            ? (new FromDto())
                ->setId($data['id'])
                ->setIsBot($data['is_bot'])
                ->setUsername($data['username'] ?? null)
                ->setFirstName($data['first_name'] ?? null)
                ->setLastName($data['last_name'] ?? null)
                ->setLanguageCode($data['language_code'] ?? null)
            : null;
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
