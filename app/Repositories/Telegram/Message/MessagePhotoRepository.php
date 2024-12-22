<?php

namespace App\Repositories\Telegram\Message;

use App\Dto\Telegram\Message\PhotoDto;
use App\Dto\Telegram\MessagePhotoDto;
use App\Repositories\Telegram\Request\MessageRepository;

class MessagePhotoRepository extends MessageRepository
{
    public function createDto(?array $data = null): MessagePhotoDto
    {
        $data = $data ?? $this->payload;

        return (new MessagePhotoDto())
            ->setId($data['message_id'])
            ->setFrom($this->getFromDto($data['from']))
            ->setChat($this->getChatDto($data['chat']))
            ->setDate($data['date'])
            ->setPhoto($this->getPhotoItems($data['photo']));
    }

    /**
     * @param array $data
     * @return PhotoDto[]
     */
    private function getPhotoItems(array $data): array
    {
        $photoItems = [];

        foreach ($data as $dataItem) {
            $photoItems[] = (new PhotoDto())
                ->setFileId($dataItem['file_id'])
                ->setFileUniqueId($dataItem['file_unique_id'])
                ->setFileSize($dataItem['file_size'])
                ->setWidth($dataItem['width'])
                ->setHeight($dataItem['height']);
        }

        return $photoItems;
    }
}
