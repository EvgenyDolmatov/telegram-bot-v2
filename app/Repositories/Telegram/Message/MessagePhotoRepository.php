<?php

namespace App\Repositories\Telegram\Message;

use App\Dto\Telegram\Message\ChatDto;
use App\Dto\Telegram\Message\FromDto;
use App\Dto\Telegram\Message\PhotoDto;
use App\Dto\Telegram\MessagePhotoDto;
use App\Models\TrashMessage;
use App\Repositories\Telegram\MessageRepository;
use App\Repositories\Telegram\RepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

readonly class MessagePhotoRepository extends MessageRepository
{
    /**
     * @throws Exception
     */
    public function createDto(?array $data = null): MessagePhotoDto
    {
        $data = $data ?: $this->request->get("message");

        try {
            $dto = (new MessagePhotoDto())
                ->setId($data['message_id'])
                ->setFrom($this->getFromDto($data['from']))
                ->setChat($this->getChatDto($data['chat']))
                ->setDate($data['date'])
                ->setPhoto($this->getPhotoItems($data['photo']));
        } catch (Exception $e) {
            Log::error('Some problem occurred with MessagePhotoRepository.', [
                'message' => $e->getMessage(),
            ]);
            throw new Exception('Some problem occurred with MessagePhotoRepository.');
        }

        return $dto;
    }

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
